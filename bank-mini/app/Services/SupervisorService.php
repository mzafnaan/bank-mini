<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\JournalEntry;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SupervisorService
{
    /**
     * Get Supervisor Dashboard operational statistics.
     */
    public function getDashboardStats(): array
    {
        $today = date('Y-m-d');

        $pendingReportsCount = DailyReport::where('status', 'draft')->count();
        $approvedReportsCount = DailyReport::where('status', 'approved')->count();
        $rejectedReportsCount = DailyReport::where('status', 'rejected')->count();
        $totalTellers = User::where('role', 'teller')->count();
        $todayTransactionsCount = Transaction::whereDate('created_at', $today)->count();

        $pendingReports = DailyReport::with('teller')
            ->where('status', 'draft')
            ->orderBy('report_date', 'desc')
            ->take(5)
            ->get();

        $recentJournals = JournalEntry::with(['transaction.bankAccount.customer', 'transaction.teller'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return [
            'pending_reports_count' => $pendingReportsCount,
            'approved_reports_count' => $approvedReportsCount,
            'rejected_reports_count' => $rejectedReportsCount,
            'total_tellers' => $totalTellers,
            'today_transactions_count' => $todayTransactionsCount,
            'pending_reports' => $pendingReports,
            'recent_journals' => $recentJournals,
        ];
    }

    /**
     * Get list of daily reports with search and status filter.
     */
    public function getDailyReports(?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        $query = DailyReport::with(['teller', 'supervisor'])
            ->orderBy('report_date', 'desc')
            ->orderBy('created_at', 'desc');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('report_date', 'like', "%{$search}%")
                  ->orWhereHas('teller', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        if (! empty($status) && in_array($status, ['draft', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        return $query->paginate(15);
    }

    /**
     * Get detail of a specific daily report including teller's transactions.
     */
    public function getDailyReportDetail(DailyReport $dailyReport): array
    {
        $dailyReport->load(['teller', 'supervisor']);

        $transactions = Transaction::with(['bankAccount.customer'])
            ->where('teller_id', $dailyReport->teller_id)
            ->whereDate('created_at', $dailyReport->report_date)
            ->orderBy('created_at', 'asc')
            ->get();

        return [
            'report' => $dailyReport,
            'transactions' => $transactions,
        ];
    }

    /**
     * Approve a teller's daily report.
     */
    public function approveDailyReport(DailyReport $dailyReport, int $supervisorId): DailyReport
    {
        if ($dailyReport->status === 'approved') {
            throw new Exception('Laporan harian ini telah disetujui sebelumnya.');
        }

        $dailyReport->update([
            'supervisor_id' => $supervisorId,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return $dailyReport->fresh(['teller', 'supervisor']);
    }

    /**
     * Reject a teller's daily report.
     */
    public function rejectDailyReport(DailyReport $dailyReport, int $supervisorId): DailyReport
    {
        if ($dailyReport->status === 'rejected') {
            throw new Exception('Laporan harian ini telah ditolak sebelumnya.');
        }

        $dailyReport->update([
            'supervisor_id' => $supervisorId,
            'status' => 'rejected',
            'approved_at' => now(),
        ]);

        return $dailyReport->fresh(['teller', 'supervisor']);
    }
}
