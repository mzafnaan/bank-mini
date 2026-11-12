<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerAccount;
use App\Models\WithdrawalRequest;
use App\Services\CustomerWithdrawalService;
use App\Services\SimpleXlsxExportService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NasabahApiController extends Controller
{
    /**
     * Dashboard data: balance, account info, totals, recent transactions.
     */
    public function dashboard(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customerAccount->load(['customer.bankAccount']);

        $customer = $customerAccount->customer;
        $bankAccount = $customer->bankAccount;

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tidak ditemukan.',
            ], 404);
        }

        // Get totals
        $totalDeposit = $bankAccount->transactions()
            ->where('type', 'deposit')
            ->sum('amount');

        $totalWithdrawal = $bankAccount->transactions()
            ->where('type', 'withdrawal')
            ->sum('amount');

        // Get recent 5 transactions
        $recentTransactions = $bankAccount->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($trx) use ($bankAccount, $customer) {
                return [
                    'id' => $trx->id,
                    'transaction_code' => $trx->code,
                    'type' => $trx->type === 'deposit' ? 'setoran' : 'penarikan',
                    'amount' => $trx->amount,
                    'account_number' => $bankAccount->account_number,
                    'customer_name' => $customer->name,
                    'created_at' => $trx->created_at->toIso8601String(),
                ];
            });

        // Active pending withdrawal request waiting for customer PIN authorization
        $pendingWithdrawal = WithdrawalRequest::with('teller')
            ->where('bank_account_id', $bankAccount->id)
            ->where('status', 'waiting')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('created_at')
            ->first();

        $pendingData = null;
        if ($pendingWithdrawal) {
            $secondsRemaining = $pendingWithdrawal->expires_at
                ? (int) max(0, round(now()->diffInSeconds($pendingWithdrawal->expires_at, false)))
                : 900;

            $pendingData = [
                'id' => $pendingWithdrawal->id,
                'amount' => $pendingWithdrawal->amount,
                'teller_name' => $pendingWithdrawal->teller?->name ?? 'Petugas Teller',
                'status' => $pendingWithdrawal->status,
                'expires_at' => $pendingWithdrawal->expires_at?->toIso8601String(),
                'seconds_remaining' => $secondsRemaining,
                'created_at' => $pendingWithdrawal->created_at?->toIso8601String(),
            ];
        }

        $pendingDeactivation = null;
        if ($customer->deactivation_requested_at) {
            $pendingDeactivation = [
                'is_pending' => true,
                'requested_at' => $customer->deactivation_requested_at->toIso8601String(),
                'reason' => $customer->deactivation_reason ?: 'Administrator telah mengajukan penonaktifan rekening Anda.',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'customer_name' => $customer->name,
                'account_number' => $bankAccount->account_number,
                'balance' => $bankAccount->balance,
                'total_deposit' => (int) $totalDeposit,
                'total_withdrawal' => (int) $totalWithdrawal,
                'recent_transactions' => $recentTransactions,
                'pending_withdrawal' => $pendingData,
                'pending_deactivation' => $pendingDeactivation,
            ],
        ], 200);
    }

    /**
     * Transaction history with pagination.
     */
    public function transactionHistory(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customerAccount->load(['customer.bankAccount']);

        $bankAccount = $customerAccount->customer->bankAccount;

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tidak ditemukan.',
            ], 404);
        }

        $customer = $customerAccount->customer;

        $transactions = $bankAccount->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        // Calculate running balance for each transaction
        $items = collect($transactions->items())->map(function ($trx) use ($bankAccount, $customer) {
            // Get balance after this transaction by calculating from current balance
            $laterTransactionsNet = $bankAccount->transactions()
                ->where('created_at', '>', $trx->created_at)
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as net
                ")
                ->value('net');

            $balanceAfter = $bankAccount->balance - $laterTransactionsNet;
            $balanceBefore = $trx->type === 'deposit'
                ? $balanceAfter - $trx->amount
                : $balanceAfter + $trx->amount;

            return [
                'id' => $trx->id,
                'transaction_code' => $trx->code,
                'type' => $trx->type === 'deposit' ? 'setoran' : 'penarikan',
                'amount' => $trx->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'account_number' => $bankAccount->account_number,
                'customer_name' => $customer->name,
                'created_at' => $trx->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $items,
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ], 200);
    }

    /**
     * Transaction detail by ID.
     */
    public function transactionDetail(Request $request, int $id): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customerAccount->load(['customer.bankAccount']);

        $bankAccount = $customerAccount->customer->bankAccount;

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tidak ditemukan.',
            ], 404);
        }

        $customer = $customerAccount->customer;

        $trx = $bankAccount->transactions()->find($id);

        if (!$trx) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        // Calculate balance before and after
        $laterTransactionsNet = $bankAccount->transactions()
            ->where('created_at', '>', $trx->created_at)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) as net
            ")
            ->value('net');

        $balanceAfter = $bankAccount->balance - $laterTransactionsNet;
        $balanceBefore = $trx->type === 'deposit'
            ? $balanceAfter - $trx->amount
            : $balanceAfter + $trx->amount;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $trx->id,
                'transaction_code' => $trx->code,
                'type' => $trx->type === 'deposit' ? 'setoran' : 'penarikan',
                'amount' => $trx->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'account_number' => $bankAccount->account_number,
                'customer_name' => $customer->name,
                'description' => $trx->description ?? null,
                'created_at' => $trx->created_at->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Account/Rekening information.
     */
    public function accountInfo(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customerAccount->load(['customer.bankAccount']);

        $customer = $customerAccount->customer;
        $bankAccount = $customer->bankAccount;

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tidak ditemukan.',
            ], 404);
        }

        // Get transaction stats
        $totalDeposit = $bankAccount->transactions()
            ->where('type', 'deposit')
            ->sum('amount');

        $totalWithdrawal = $bankAccount->transactions()
            ->where('type', 'withdrawal')
            ->sum('amount');

        $transactionCount = $bankAccount->transactions()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => [
                    'name' => $customer->name,
                    'nis' => $customer->nis,
                    'class' => $customer->class,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'email_verified_at' => $customer->email_verified_at?->toIso8601String(),
                ],
                'account' => [
                    'account_number' => $bankAccount->account_number,
                    'balance' => $bankAccount->balance,
                    'status' => $bankAccount->status,
                    'created_at' => $bankAccount->created_at->toIso8601String(),
                ],
                'statistics' => [
                    'total_deposit' => (int) $totalDeposit,
                    'total_withdrawal' => (int) $totalWithdrawal,
                    'transaction_count' => $transactionCount,
                ],
            ],
        ], 200);
    }

    /**
     * Get active pending withdrawal request.
     */
    public function pendingWithdrawalRequest(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customerAccount->load(['customer.bankAccount']);

        $bankAccount = $customerAccount->customer?->bankAccount;
        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tidak ditemukan.',
            ], 404);
        }

        $pendingWithdrawal = WithdrawalRequest::with('teller')
            ->where('bank_account_id', $bankAccount->id)
            ->where('status', 'waiting')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('created_at')
            ->first();

        if (!$pendingWithdrawal) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        $secondsRemaining = $pendingWithdrawal->expires_at
            ? (int) max(0, round(now()->diffInSeconds($pendingWithdrawal->expires_at, false)))
            : 900;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pendingWithdrawal->id,
                'amount' => $pendingWithdrawal->amount,
                'teller_name' => $pendingWithdrawal->teller?->name ?? 'Petugas Teller',
                'status' => $pendingWithdrawal->status,
                'expires_at' => $pendingWithdrawal->expires_at?->toIso8601String(),
                'seconds_remaining' => $secondsRemaining,
                'created_at' => $pendingWithdrawal->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Authorize a pending withdrawal request via PIN.
     */
    public function authorizeWithdrawal(Request $request, WithdrawalRequest $withdrawalRequest): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'digits:6'],
        ], [
            'pin.required' => 'PIN otorisasi 6 digit wajib dimasukkan.',
            'pin.digits' => 'PIN otorisasi harus terdiri dari 6 digit angka.',
        ]);

        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();

        try {
            $service = app(CustomerWithdrawalService::class);
            $transaction = $service->authorize(
                $customerAccount,
                $withdrawalRequest,
                $request->input('pin')
            );

            $bankAccount = $customerAccount->customer->bankAccount->fresh();

            return response()->json([
                'success' => true,
                'message' => 'Penarikan tunai sebesar Rp ' . number_format($withdrawalRequest->amount, 0, ',', '.') . ' berhasil disetujui.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'transaction_code' => $transaction->code,
                    'amount' => $transaction->amount,
                    'balance_after' => $bankAccount->balance,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['pin'][0] ?? 'PIN yang dimasukkan tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reject a pending withdrawal request.
     */
    public function rejectWithdrawal(Request $request, WithdrawalRequest $withdrawalRequest): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();

        try {
            $service = app(CustomerWithdrawalService::class);
            $service->reject(
                $customerAccount,
                $withdrawalRequest
            );

            return response()->json([
                'success' => true,
                'message' => 'Permintaan penarikan tunai berhasil ditolak.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get monthly financial statistics for Mobile Analytics Dashboard (Donut/Pie Chart).
     */
    public function stats(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customerAccount->load(['customer.bankAccount']);
        $bankAccount = $customerAccount->customer?->bankAccount;

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tidak ditemukan.',
            ], 404);
        }

        // Selected month (format: YYYY-MM, e.g., '2026-11')
        $monthInput = $request->query('month', date('Y-m'));
        $time = strtotime($monthInput . '-01');
        if (!$time) {
            $monthInput = date('Y-m');
            $time = strtotime($monthInput . '-01');
        }

        $startDate = date('Y-m-01', $time);
        $endDate = date('Y-m-t', $time);
        $monthName = \Carbon\Carbon::parse($startDate)->translatedFormat('F Y');

        $monthTransactions = $bankAccount->transactions()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalDeposit = (int) $monthTransactions->where('type', 'deposit')->sum('amount');
        $totalWithdrawal = (int) $monthTransactions->where('type', 'withdrawal')->sum('amount');
        $totalVolume = $totalDeposit + $totalWithdrawal;

        $depositPct = $totalVolume > 0 ? round(($totalDeposit / $totalVolume) * 100, 1) : 0;
        $withdrawalPct = $totalVolume > 0 ? round(($totalWithdrawal / $totalVolume) * 100, 1) : 0;

        $breakdown = [];
        if ($totalDeposit > 0) {
            $breakdown[] = [
                'type' => 'deposit',
                'name' => 'Setoran Tunai (Pemasukan)',
                'amount' => $totalDeposit,
                'percentage' => $depositPct,
                'color' => '#22C55E', // Green
            ];
        }
        if ($totalWithdrawal > 0) {
            $breakdown[] = [
                'type' => 'withdrawal',
                'name' => 'Penarikan Tunai (Pengeluaran)',
                'amount' => $totalWithdrawal,
                'percentage' => $withdrawalPct,
                'color' => '#EF4444', // Red/Coral
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $monthInput,
                'period_label' => $monthName,
                'current_balance' => (int) $bankAccount->balance,
                'total_income' => $totalDeposit,
                'total_expense' => $totalWithdrawal,
                'net_cashflow' => $totalDeposit - $totalWithdrawal,
                'deposit_percentage' => $depositPct,
                'withdrawal_percentage' => $withdrawalPct,
                'transaction_count' => $monthTransactions->count(),
                'breakdown' => $breakdown,
                'transactions' => $monthTransactions->take(15)->map(fn($t) => [
                    'id' => $t->id,
                    'code' => $t->code,
                    'type' => $t->type,
                    'amount' => (int) $t->amount,
                    'created_at' => $t->created_at->toIso8601String(),
                ]),
            ],
        ], 200);
    }

    /**
     * Export customer mutations to real OpenXML Excel format (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customer = $customerAccount->customer;
        $bankAccount = $customer->bankAccount;

        $type = $request->query('type');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $openingBalance = 0;
        if (!empty($startDate)) {
            $priorDeposits = $bankAccount->transactions()
                ->where('type', 'deposit')
                ->whereDate('created_at', '<', $startDate)
                ->sum('amount');
            $priorWithdrawals = $bankAccount->transactions()
                ->where('type', 'withdrawal')
                ->whereDate('created_at', '<', $startDate)
                ->sum('amount');
            $openingBalance = $priorDeposits - $priorWithdrawals;
        }

        $query = $bankAccount->transactions()
            ->with('teller')
            ->orderBy('created_at', 'asc');

        if (!empty($type) && in_array($type, ['deposit', 'withdrawal'], true)) {
            $query->where('type', $type);
        }
        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->get();
        $accountNo = $bankAccount?->account_number ?? 'Rekening';
        $filename = 'Mutasi_' . $accountNo . '_' . date('Ymd_His') . '.xlsx';

        $periodLabel = (!empty($startDate) && !empty($endDate))
            ? date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate))
            : (!empty($startDate) ? 'Mulai ' . date('d/m/Y', strtotime($startDate)) : (!empty($endDate) ? 'Sampai ' . date('d/m/Y', strtotime($endDate)) : 'Semua Periode'));

        $metaPairs = [
            [
                'L_label' => 'Nama Nasabah',
                'L_val' => $customer->name,
                'R_label' => 'No. Rekening',
                'R_val' => $bankAccount?->account_number ?? '-',
                'R_is_acc' => true,
            ],
            [
                'L_label' => 'NIS / Kelas',
                'L_val' => ($customer->nis ?? '-') . ' / ' . ($customer->class ?? '') . ' ' . ($customer->class_group ?? ''),
                'R_label' => 'Periode Mutasi',
                'R_val' => $periodLabel,
            ],
            [
                'L_label' => 'Tanggal Export',
                'L_val' => date('d/m/Y H:i:s'),
                'R_label' => 'Status Rekening',
                'R_val' => $bankAccount?->status === 'active' ? 'Aktif' : 'Nonaktif',
            ],
        ];

        $headers = ['No', 'Tanggal & Waktu', 'ID Transaksi', 'Tipe Transaksi', 'Setoran (Rp)', 'Penarikan (Rp)', 'Saldo Akhir (Rp)', 'Petugas Teller'];

        $rows = [];
        $rows[] = [
            ['v' => '-', 's' => 8],
            ['v' => $startDate ? date('d/m/Y 00:00', strtotime($startDate)) : '-', 's' => 8],
            ['v' => 'SALDO-AWAL', 's' => 7],
            ['v' => 'Saldo Awal Periode', 's' => 7],
            ['v' => 0, 's' => 9],
            ['v' => 0, 's' => 9],
            ['v' => $openingBalance, 's' => 10],
            ['v' => 'Sistem', 's' => 8],
        ];

        $runningBalance = $openingBalance;
        $totalDeposit = 0;
        $totalWithdrawal = 0;
        $no = 1;

        foreach ($transactions as $t) {
            $isDeposit = ($t->type === 'deposit');
            if ($isDeposit) {
                $runningBalance += $t->amount;
                $totalDeposit += $t->amount;
                $creditVal = (int) $t->amount;
                $debitVal = 0;
            } else {
                $runningBalance -= $t->amount;
                $totalWithdrawal += $t->amount;
                $creditVal = 0;
                $debitVal = (int) $t->amount;
            }

            $rows[] = [
                ['v' => $no++, 's' => 8],
                ['v' => $t->created_at ? $t->created_at->format('d/m/Y H:i') : '-', 's' => 8],
                ['v' => $t->code, 's' => 7],
                ['v' => $isDeposit ? 'Setoran Tunai' : 'Penarikan Tunai', 's' => 7],
                ['v' => $creditVal, 's' => 9],
                ['v' => $debitVal, 's' => 9],
                ['v' => $runningBalance, 's' => 10],
                ['v' => $t->teller?->name ?? '-', 's' => 8],
            ];
        }

        $summary = [
            ['v' => 'TOTAL PERIODE', 's' => 11],
            ['v' => '', 's' => 11],
            ['v' => '', 's' => 11],
            ['v' => '', 's' => 11],
            ['v' => $totalDeposit, 's' => 12],
            ['v' => $totalWithdrawal, 's' => 12],
            ['v' => $runningBalance, 's' => 12],
            ['v' => '-', 's' => 11],
        ];

        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_');
        SimpleXlsxExportService::create($tempPath, $metaPairs, $headers, $rows, $summary);

        $downloadHeaders = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ];

        return response()->download($tempPath, $filename, $downloadHeaders)->deleteFileAfterSend(true);
    }

    /**
     * Return minimalist print view HTML for mobile webview or download.
     */
    public function printReport(Request $request)
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customer = $customerAccount->customer;
        $bankAccount = $customer->bankAccount;

        $type = $request->query('type');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $openingBalance = 0;
        if (!empty($startDate)) {
            $priorDeposits = $bankAccount->transactions()
                ->where('type', 'deposit')
                ->whereDate('created_at', '<', $startDate)
                ->sum('amount');
            $priorWithdrawals = $bankAccount->transactions()
                ->where('type', 'withdrawal')
                ->whereDate('created_at', '<', $startDate)
                ->sum('amount');
            $openingBalance = $priorDeposits - $priorWithdrawals;
        }

        $query = $bankAccount->transactions()
            ->with('teller')
            ->orderBy('created_at', 'asc');

        if (!empty($type) && in_array($type, ['deposit', 'withdrawal'], true)) {
            $query->where('type', $type);
        }
        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->get();
        $runningBalance = $openingBalance;
        $totalDeposit = 0;
        $totalWithdrawal = 0;

        $items = $transactions->map(function ($t) use (&$runningBalance, &$totalDeposit, &$totalWithdrawal) {
            if ($t->type === 'deposit') {
                $runningBalance += $t->amount;
                $totalDeposit += $t->amount;
            } else {
                $runningBalance -= $t->amount;
                $totalWithdrawal += $t->amount;
            }
            $t->running_balance = $runningBalance;
            return $t;
        });

        $closingBalance = $runningBalance;
        $periodLabel = (!empty($startDate) && !empty($endDate))
            ? date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate))
            : (!empty($startDate) ? 'Mulai ' . date('d/m/Y', strtotime($startDate)) : (!empty($endDate) ? 'Sampai ' . date('d/m/Y', strtotime($endDate)) : 'Semua Periode'));

        return response()
            ->view('customer.print_transactions', compact(
                'customer',
                'bankAccount',
                'items',
                'openingBalance',
                'closingBalance',
                'totalDeposit',
                'totalWithdrawal',
                'startDate',
                'endDate',
                'periodLabel',
                'type'
            ))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

    /**
     * Customer approves deactivation requested by Admin.
     */
    public function approveDeactivation(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customer = $customerAccount->customer()->first();

        if (!$customer || !$customer->deactivation_requested_at) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada pengajuan penonaktifan rekening yang aktif.',
            ], 400);
        }

        app(\App\Services\CustomerService::class)->approveDeactivation($customer);
        $customerAccount->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rekening Anda telah resmi dinonaktifkan atas persetujuan Anda.',
        ], 200);
    }

    /**
     * Customer rejects deactivation requested by Admin.
     */
    public function rejectDeactivation(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customer = $customerAccount->customer()->first();

        if (!$customer || !$customer->deactivation_requested_at) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada pengajuan penonaktifan rekening yang aktif.',
            ], 400);
        }

        app(\App\Services\CustomerService::class)->rejectDeactivation($customer);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan penonaktifan rekening berhasil ditolak. Rekening Anda tetap aktif.',
        ], 200);
    }
}

