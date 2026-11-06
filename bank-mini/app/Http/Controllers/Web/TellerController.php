<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teller\DailyReportRequest;
use App\Http\Requests\Teller\DepositRequest;
use App\Http\Requests\Teller\WithdrawalRequest as TellerWithdrawalRequest;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use App\Services\TellerService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TellerController extends Controller
{
    public function __construct(
        protected TellerService $tellerService
    ) {}

    /**
     * Teller Dashboard overview.
     */
    public function dashboard(Request $request): View
    {
        $tellerId = auth()->id();
        $stats = $this->tellerService->getDashboardStats($tellerId);
        $search = trim($request->input('search', ''));
        $searchedAccount = null;

        if ($search !== '') {
            $searchedAccount = $this->tellerService->identifyAccount($search);
        }

        return view('teller.dashboard', compact('stats', 'search', 'searchedAccount'));
    }

    /**
     * Account Identification view.
     */
    public function identification(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $account = null;

        if ($search !== '') {
            $account = $this->tellerService->identifyAccount($search);
        }

        return view('teller.identification', compact('account', 'search'));
    }

    /**
     * Cash Deposit form view.
     */
    public function depositForm(Request $request): View
    {
        $accountNumber = trim($request->input('account_number', ''));
        $account = null;

        if ($accountNumber !== '') {
            $account = $this->tellerService->identifyAccount($accountNumber);
        }

        return view('teller.deposit', compact('account', 'accountNumber'));
    }

    /**
     * Process Cash Deposit.
     */
    public function storeDeposit(DepositRequest $request): RedirectResponse
    {
        try {
            $transaction = $this->tellerService->processDeposit(
                auth()->id(),
                $request->input('account_number'),
                $request->input('amount'),
                $request->input('notes')
            );

            return redirect()->route('teller.transactions.receipt', $transaction->id)
                ->with('success', 'Transaksi setoran tunai sebesar Rp ' . number_format($transaction->amount, 0, ',', '.') . ' berhasil diproses.');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cash Withdrawal form view.
     */
    public function withdrawalForm(Request $request): View
    {
        $accountNumber = trim($request->input('account_number', ''));
        $account = null;

        if ($accountNumber !== '') {
            $account = $this->tellerService->identifyAccount($accountNumber);
        }

        $activeRequest = null;
        if ($account) {
            $activeRequest = WithdrawalRequest::where('bank_account_id', $account->id)
                ->where('status', 'waiting')
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->latest('created_at')
                ->first();
        }

        return view('teller.withdrawal', compact('account', 'accountNumber', 'activeRequest'));
    }

    /**
     * Process Cash Withdrawal with Customer PIN Verification.
     */
    public function storeWithdrawal(TellerWithdrawalRequest $request): RedirectResponse
    {
        try {
            $transaction = $this->tellerService->processWithdrawal(
                auth()->id(),
                $request->input('account_number'),
                $request->input('amount'),
                $request->input('pin')
            );

            return redirect()->route('teller.transactions.receipt', $transaction->id)
                ->with('success', 'Transaksi penarikan tunai sebesar Rp ' . number_format($transaction->amount, 0, ',', '.') . ' berhasil diproses.');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Submit a pending Withdrawal Request for mobile customer authorization.
     */
    public function storeWithdrawalRequest(Request $request)
    {
        $request->validate([
            'account_number' => ['required', 'string', 'exists:bank_accounts,account_number'],
            'amount' => ['required', 'numeric', 'min:1000'],
        ]);

        try {
            $withdrawalRequest = $this->tellerService->createWithdrawalRequest(
                auth()->id(),
                $request->input('account_number'),
                $request->input('amount')
            );

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Permintaan penarikan #{$withdrawalRequest->id} berhasil dibuat. Menunggu otorisasi PIN dari Nasabah.",
                    'data' => [
                        'id' => $withdrawalRequest->id,
                        'account_number' => $request->input('account_number'),
                        'amount' => $withdrawalRequest->amount,
                        'formatted_amount' => 'Rp ' . number_format($withdrawalRequest->amount, 0, ',', '.'),
                        'expires_at' => $withdrawalRequest->expires_at?->toIso8601String(),
                    ],
                ]);
            }

            return back()->with('success', "Permintaan penarikan #{$withdrawalRequest->id} berhasil dibuat. Menunggu otorisasi PIN dari Nasabah.");
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Check status of a withdrawal request (used for teller polling).
     */
    public function checkWithdrawalStatus(WithdrawalRequest $withdrawalRequest): JsonResponse
    {
        // Expire if past expires_at
        if ($withdrawalRequest->status === 'waiting' && $withdrawalRequest->expires_at && $withdrawalRequest->expires_at->isPast()) {
            $withdrawalRequest->update(['status' => 'expired']);
        }

        $receiptUrl = null;
        if ($withdrawalRequest->status === 'approved') {
            $transaction = Transaction::where('bank_account_id', $withdrawalRequest->bank_account_id)
                ->where('teller_id', $withdrawalRequest->teller_id)
                ->where('type', 'withdrawal')
                ->where('amount', $withdrawalRequest->amount)
                ->where('created_at', '>=', $withdrawalRequest->created_at)
                ->latest('id')
                ->first();

            if ($transaction) {
                $receiptUrl = route('teller.transactions.receipt', $transaction->id);
            }
        }

        return response()->json([
            'id' => $withdrawalRequest->id,
            'status' => $withdrawalRequest->status,
            'receipt_url' => $receiptUrl,
        ]);
    }

    /**
     * Cancel a waiting withdrawal request.
     */
    public function cancelWithdrawalRequest(WithdrawalRequest $withdrawalRequest): JsonResponse
    {
        if ($withdrawalRequest->status === 'waiting') {
            $withdrawalRequest->update(['status' => 'rejected']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permintaan penarikan berhasil dibatalkan.',
        ]);
    }

    /**
     * List of Teller Transactions.
     */
    public function transactions(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $type = $request->input('type');
        $date = $request->input('date');

        $transactions = $this->tellerService->getTellerTransactions(auth()->id(), $search, $type, $date);

        return view('teller.transactions', compact('transactions', 'search', 'type', 'date'));
    }

    /**
     * Print / View Receipt Struk.
     */
    public function receipt(Transaction $transaction): View
    {
        $transaction->load(['bankAccount.customer', 'teller']);
        return view('teller.receipt', compact('transaction'));
    }

    /**
     * Daily Closing / Penutupan Kas Harian.
     */
    public function dailyReport(Request $request): View
    {
        $reportData = $this->tellerService->getDailyReportData(auth()->id());
        return view('teller.daily_report', compact('reportData'));
    }

    /**
     * Submit Daily Closing Report for Supervisor Approval.
     */
    public function storeDailyReport(DailyReportRequest $request): RedirectResponse
    {
        try {
            $report = $this->tellerService->submitDailyReport(
                auth()->id(),
                $request->input('physical_cash')
            );

            return redirect()->route('teller.daily-report')
                ->with('success', 'Laporan penutupan kas harian berhasil dibuat dan dikirim ke Supervisor (Status: Draft).');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}