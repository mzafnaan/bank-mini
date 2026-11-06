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

        return $query->paginate(25);
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

    /**
     * Get transaction report filtered by period: daily, weekly, or monthly.
     *
     * @param string $period 'daily', 'weekly', or 'monthly'
     * @param string|null $date Date for daily (Y-m-d)
     * @param string|null $startDate Start date for weekly (Y-m-d)
     * @param string|null $endDate End date for weekly (Y-m-d)
     * @param string|null $month Month for monthly (1-12)
     * @param string|null $year Year for monthly (Y)
     * @param string|null $type 'all', 'deposit', 'withdrawal'
     */
    public function getTransactionReport(
        string $period = 'daily',
        ?string $date = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $month = null,
        ?string $year = null,
        ?string $type = null
    ): array {
        $now = now();
        $today = $now->toDateString();

        $date = $date ?: $today;
        $startDate = $startDate ?: $now->copy()->startOfWeek()->toDateString();
        $endDate = $endDate ?: $now->copy()->endOfWeek()->toDateString();

        // Enforce maximum date up to today (prevent future dates)
        if ($date > $today) {
            $date = $today;
        }
        if ($startDate > $today) {
            $startDate = $today;
        }
        if ($endDate > $today) {
            $endDate = $today;
        }
        if ($startDate > $endDate) {
            $startDate = $endDate;
        }

        $month = $month ?: (string) $now->month;
        $year = $year ?: (string) $now->year;
        $monthNum = (int) $month;
        $yearNum = (int) $year;

        // Enforce maximum month/year up to current month (prevent future periods)
        if ($yearNum > (int) $now->year) {
            $yearNum = (int) $now->year;
            $year = (string) $yearNum;
        }
        if ($yearNum === (int) $now->year && $monthNum > (int) $now->month) {
            $monthNum = (int) $now->month;
            $month = (string) $monthNum;
        }

        $query = Transaction::with(['bankAccount.customer', 'teller'])
            ->orderBy('created_at', 'desc');

        $periodLabel = '';

        if ($period === 'weekly') {
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ]);
            $periodLabel = 'Mingguan (' . date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate)) . ')';
        } elseif ($period === 'monthly') {
            $monthNum = (int) $month;
            $yearNum = (int) $year;
            $query->whereYear('created_at', $yearNum)
                  ->whereMonth('created_at', $monthNum);
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $periodLabel = 'Bulanan (' . ($monthNames[$monthNum] ?? $month) . ' ' . $yearNum . ')';
        } else {
            $period = 'daily';
            $query->whereDate('created_at', $date);
            $periodLabel = 'Harian (' . date('d/m/Y', strtotime($date)) . ')';
        }

        if (! empty($type) && in_array($type, ['deposit', 'withdrawal'], true)) {
            $query->where('type', $type);
        }

        $transactions = $query->get();

        $depositSum = (float) $transactions->where('type', 'deposit')->sum('amount');
        $depositCount = $transactions->where('type', 'deposit')->count();
        $withdrawalSum = (float) $transactions->where('type', 'withdrawal')->sum('amount');
        $withdrawalCount = $transactions->where('type', 'withdrawal')->count();
        $netCash = $depositSum - $withdrawalSum;

        return [
            'period' => $period,
            'period_label' => $periodLabel,
            'date' => $date,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'month' => $month,
            'year' => $year,
            'type' => $type ?: 'all',
            'transactions' => $transactions,
            'total_count' => $transactions->count(),
            'total_deposit' => $depositSum,
            'count_deposit' => $depositCount,
            'total_withdrawal' => $withdrawalSum,
            'count_withdrawal' => $withdrawalCount,
            'net_cash' => $netCash,
        ];
    }
}
