<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Services\JournalService;
use App\Services\SupervisorService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupervisorController extends Controller
{
    public function __construct(
        protected SupervisorService $supervisorService,
        protected JournalService $journalService
    ) {}

    /**
     * Supervisor Dashboard Overview.
     */
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $stats = $this->supervisorService->getDashboardStats();

        $searchResults = null;
        if ($search !== '') {
            $searchResults = [
                'reports' => $this->supervisorService->getDailyReports($search),
                'journals' => $this->journalService->getAllJournals($search),
            ];
        }

        return view('supervisor.dashboard', compact('stats', 'search', 'searchResults'));
    }

    /**
     * Daily Reports Verification & Management.
     */
    public function reports(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $status = trim($request->input('status', ''));
        $reports = $this->supervisorService->getDailyReports($search, $status);

        return view('supervisor.reports', compact('reports', 'search', 'status'));
    }

    /**
     * Display detail of a specific daily report.
     */
    public function show(DailyReport $dailyReport): View
    {
        $detailData = $this->supervisorService->getDailyReportDetail($dailyReport);
        return view('supervisor.report_detail', $detailData);
    }

    /**
     * Approve a teller's daily report.
     */
    public function approveReport(DailyReport $dailyReport): RedirectResponse
    {
        try {
            $approvedReport = $this->supervisorService->approveDailyReport($dailyReport, auth()->id());
            $formattedDate = $approvedReport->report_date ? $approvedReport->report_date->format('d/m/Y') : '-';
            
            return back()->with('success', "Laporan harian teller '{$approvedReport->teller?->name}' tanggal {$formattedDate} berhasil disetujui.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a teller's daily report.
     */
    public function rejectReport(DailyReport $dailyReport): RedirectResponse
    {
        try {
            $rejectedReport = $this->supervisorService->rejectDailyReport($dailyReport, auth()->id());
            $formattedDate = $rejectedReport->report_date ? $rejectedReport->report_date->format('d/m/Y') : '-';
            
            return back()->with('success', "Laporan harian teller '{$rejectedReport->teller?->name}' tanggal {$formattedDate} telah ditolak.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Accounting Audit Journals View.
     */
    public function journals(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $journals = $this->journalService->getAllJournals($search);

        return view('supervisor.journals', compact('journals', 'search'));
    }

    /**
     * Transaction Report (Daily, Weekly, Monthly) View.
     */
    public function transactionsReport(Request $request): View
    {
        $period = $request->input('period', 'daily');
        $date = $request->input('date');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $month = $request->input('month');
        $year = $request->input('year');
        $type = $request->input('type');

        $reportData = $this->supervisorService->getTransactionReport(
            $period,
            $date,
            $startDate,
            $endDate,
            $month,
            $year,
            $type
        );

        return view('supervisor.transactions_report', $reportData);
    }

    /**
     * Printable Transaction Report View.
     */
    public function printTransactionsReport(Request $request): View
    {
        $period = $request->input('period', 'daily');
        $date = $request->input('date');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $month = $request->input('month');
        $year = $request->input('year');
        $type = $request->input('type');

        $reportData = $this->supervisorService->getTransactionReport(
            $period,
            $date,
            $startDate,
            $endDate,
            $month,
            $year,
            $type
        );

        return view('supervisor.print_transactions_report', $reportData);
    }
}