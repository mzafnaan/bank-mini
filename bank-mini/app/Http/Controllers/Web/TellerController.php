<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teller\DailyReportRequest;
use App\Http\Requests\Teller\DepositRequest;
use App\Http\Requests\Teller\WithdrawalRequest as TellerWithdrawalRequest;
use App\Models\Transaction;
use App\Services\TellerService;
use Exception;
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

        return view('teller.withdrawal', compact('account', 'accountNumber'));
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
    public function storeWithdrawalRequest(Request $request): RedirectResponse
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

            return back()->with('success', "Permintaan penarikan #{$withdrawalRequest->id} berhasil dibuat. Menunggu otorisasi PIN dari Nasabah.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
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
