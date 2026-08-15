<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\DailyReport;
use App\Models\JournalEntry;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TellerService
{
    /**
     * Get Teller Dashboard operational statistics.
     */
    public function getDashboardStats(int $tellerId): array
    {
        $today = date('Y-m-d');

        $depositQuery = Transaction::where('teller_id', $tellerId)
            ->where('type', 'deposit')
            ->whereDate('created_at', $today);

        $withdrawalQuery = Transaction::where('teller_id', $tellerId)
            ->where('type', 'withdrawal')
            ->whereDate('created_at', $today);

        $totalDeposit = $depositQuery->sum('amount');
        $countDeposit = $depositQuery->count();

        $totalWithdrawal = $withdrawalQuery->sum('amount');
        $countWithdrawal = $withdrawalQuery->count();

        $dailyReport = DailyReport::where('teller_id', $tellerId)
            ->whereDate('report_date', $today)
            ->first();

        $pendingWithdrawals = WithdrawalRequest::with(['bankAccount.customer'])
            ->where('teller_id', $tellerId)
            ->where('status', 'waiting')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('created_at')
            ->take(5)
            ->get();

        $recentTransactions = Transaction::with(['bankAccount.customer'])
            ->where('teller_id', $tellerId)
            ->latest('created_at')
            ->take(10)
            ->get();

        return [
            'total_deposit' => $totalDeposit,
            'count_deposit' => $countDeposit,
            'total_withdrawal' => $totalWithdrawal,
            'count_withdrawal' => $countWithdrawal,
            'net_cash' => $totalDeposit - $totalWithdrawal,
            'daily_report' => $dailyReport,
            'pending_withdrawals' => $pendingWithdrawals,
            'recent_transactions' => $recentTransactions,
        ];
    }

    /**
     * Search and identify a bank account by Account Number, Customer NIS, Name, or QR Code content.
     */
    public function identifyAccount(string $keyword): ?BankAccount
    {
        $cleanKeyword = trim($keyword);
        if (empty($cleanKeyword)) {
            return null;
        }

        return BankAccount::with(['customer.customerAccount', 'transactions' => fn($q) => $q->latest('created_at')->take(5)])
            ->where(function ($q) use ($cleanKeyword) {
                $q->where('account_number', $cleanKeyword)
                  ->orWhere('qr_code', $cleanKeyword)
                  ->orWhereHas('customer', function ($cq) use ($cleanKeyword) {
                      $cq->where('nis', $cleanKeyword)
                        ->orWhere('name', 'like', "%{$cleanKeyword}%");
                  });
            })
            ->first();
    }

    /**
     * Process cash deposit transaction.
     */
    public function processDeposit(int $tellerId, string $accountNumber, float|int $amount, ?string $notes = null): Transaction
    {
        if ($amount <= 0) {
            throw new Exception('Nominal setoran harus lebih besar dari 0.');
        }

        $bankAccount = BankAccount::where('account_number', $accountNumber)->first();

        if (! $bankAccount) {
            throw new Exception("Rekening dengan nomor {$accountNumber} tidak ditemukan.");
        }

        return DB::transaction(function () use ($bankAccount, $tellerId, $amount) {
            // 1. Update bank account balance
            $bankAccount->increment('balance', $amount);

            // 2. Create transaction record
            $transaction = Transaction::create([
                'bank_account_id' => $bankAccount->id,
                'teller_id' => $tellerId,
                'type' => 'deposit',
                'amount' => $amount,
            ]);

            // 3. Create automatic journal entries
            // Debit: Kas (101)
            JournalEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => '101',
                'type' => 'debit',
                'amount' => $amount,
            ]);

            // Credit: Tabungan Nasabah (201)
            JournalEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => '201',
                'type' => 'credit',
                'amount' => $amount,
            ]);

            return $transaction->load(['bankAccount.customer', 'teller']);
        });
    }

    /**
     * Process cash withdrawal transaction.
     */
    public function processWithdrawal(int $tellerId, string $accountNumber, float|int $amount, string $pin): Transaction
    {
        if ($amount <= 0) {
            throw new Exception('Nominal penarikan harus lebih besar dari 0.');
        }

        $bankAccount = BankAccount::with(['customer.customerAccount'])->where('account_number', $accountNumber)->first();

        if (! $bankAccount) {
            throw new Exception("Rekening dengan nomor {$accountNumber} tidak ditemukan.");
        }

        $customerAccount = $bankAccount->customer?->customerAccount;

        if (! $customerAccount) {
            throw new Exception('Akun otorisasi nasabah belum dikonfigurasi.');
        }

        // Validate Customer PIN (BR-041)
        if (! Hash::check($pin, $customerAccount->pin)) {
            throw new Exception('PIN otorisasi nasabah tidak valid.');
        }

        // Validate Minimum Balance Constraint (BR-044: Rp 10.000 minimum balance)
        $minimumBalance = 10000;
        if (($bankAccount->balance - $amount) < $minimumBalance) {
            $formattedBalance = number_format($bankAccount->balance, 0, ',', '.');
            $formattedMin = number_format($minimumBalance, 0, ',', '.');
            throw new Exception("Saldo tidak mencukupi. Batas saldo mengendap minimal adalah Rp {$formattedMin} (Saldo saat ini: Rp {$formattedBalance}).");
        }

        return DB::transaction(function () use ($bankAccount, $tellerId, $amount) {
            // 1. Decrement balance
            $bankAccount->decrement('balance', $amount);

            // 2. Create transaction record
            $transaction = Transaction::create([
                'bank_account_id' => $bankAccount->id,
                'teller_id' => $tellerId,
                'type' => 'withdrawal',
                'amount' => $amount,
            ]);

            // 3. Create automatic journal entries
            // Debit: Tabungan Nasabah (201)
            JournalEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => '201',
                'type' => 'debit',
                'amount' => $amount,
            ]);

            // Credit: Kas (101)
            JournalEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => '101',
                'type' => 'credit',
                'amount' => $amount,
            ]);

            // 4. Update any pending withdrawal request
            WithdrawalRequest::where('bank_account_id', $bankAccount->id)
                ->where('amount', $amount)
                ->where('status', 'waiting')
                ->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);

            return $transaction->load(['bankAccount.customer', 'teller']);
        });
    }

    /**
     * Create a pending withdrawal authorization request.
     */
    public function createWithdrawalRequest(int $tellerId, string $accountNumber, float|int $amount): WithdrawalRequest
    {
        $bankAccount = BankAccount::where('account_number', $accountNumber)->firstOrFail();

        $minimumBalance = 10000;
        if (($bankAccount->balance - $amount) < $minimumBalance) {
            throw new Exception('Saldo tidak mencukupi untuk penarikan.');
        }

        return WithdrawalRequest::create([
            'bank_account_id' => $bankAccount->id,
            'teller_id' => $tellerId,
            'amount' => $amount,
            'status' => 'waiting',
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    /**
     * Get Daily Closing data and calculations.
     */
    public function getDailyReportData(int $tellerId, ?string $date = null): array
    {
        $reportDate = $date ?? date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime($reportDate . ' -1 day'));

        // Opening cash from yesterday's approved report or previous report
        $previousReport = DailyReport::where('teller_id', $tellerId)
            ->where('report_date', '<', $reportDate)
            ->orderBy('report_date', 'desc')
            ->first();

        $openingCash = $previousReport ? $previousReport->closing_cash : 0;

        $totalDeposit = Transaction::where('teller_id', $tellerId)
            ->where('type', 'deposit')
            ->whereDate('created_at', $reportDate)
            ->sum('amount');

        $totalWithdrawal = Transaction::where('teller_id', $tellerId)
            ->where('type', 'withdrawal')
            ->whereDate('created_at', $reportDate)
            ->sum('amount');

        $expectedClosingCash = $openingCash + $totalDeposit - $totalWithdrawal;

        $existingReport = DailyReport::where('teller_id', $tellerId)
            ->whereDate('report_date', $reportDate)
            ->first();

        return [
            'report_date' => $reportDate,
            'opening_cash' => $openingCash,
            'total_deposit' => $totalDeposit,
            'total_withdrawal' => $totalWithdrawal,
            'expected_closing_cash' => $expectedClosingCash,
            'existing_report' => $existingReport,
        ];
    }

    /**
     * Submit daily closing report.
     */
    public function submitDailyReport(int $tellerId, float|int $physicalCash, ?string $date = null): DailyReport
    {
        $reportData = $this->getDailyReportData($tellerId, $date);

        // Validation BR-050: Physical cash must equal calculated system cash
        if ((float) $physicalCash !== (float) $reportData['expected_closing_cash']) {
            $formattedPhysical = number_format($physicalCash, 0, ',', '.');
            $formattedExpected = number_format($reportData['expected_closing_cash'], 0, ',', '.');
            throw new Exception("Uang fisik (Rp {$formattedPhysical}) tidak sesuai dengan saldo kas sistem (Rp {$formattedExpected}). Silakan lakukan rekonsiliasi.");
        }

        return DailyReport::updateOrCreate(
            [
                'teller_id' => $tellerId,
                'report_date' => $reportData['report_date'],
            ],
            [
                'opening_cash' => $reportData['opening_cash'],
                'total_deposit' => $reportData['total_deposit'],
                'total_withdrawal' => $reportData['total_withdrawal'],
                'closing_cash' => $physicalCash,
                'status' => 'draft',
            ]
        );
    }

    /**
     * Get paginated transactions performed by teller.
     */
    public function getTellerTransactions(int $tellerId, ?string $search = null, ?string $type = null, ?string $date = null): LengthAwarePaginator
    {
        $query = Transaction::with(['bankAccount.customer'])
            ->where('teller_id', $tellerId)
            ->latest('created_at');

        if (! empty($search)) {
            $query->whereHas('bankAccount', function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                  });
            });
        }

        if (! empty($type) && in_array($type, ['deposit', 'withdrawal'])) {
            $query->where('type', $type);
        }

        if (! empty($date)) {
            $query->whereDate('created_at', $date);
        }

        return $query->paginate(15);
    }
}
