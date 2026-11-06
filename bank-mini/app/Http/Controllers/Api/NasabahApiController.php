<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerAccount;
use App\Models\WithdrawalRequest;
use App\Services\CustomerWithdrawalService;
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
}

