<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ApiLoginRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ChangePinRequest;
use App\Models\Customer;
use App\Models\CustomerAccount;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AuthApiController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Mobile login endpoint.
     */
    public function login(ApiLoginRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->loginApi($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'data' => [
                    'access_token' => $data['token'],
                    'token_type' => 'Bearer',
                    'first_login' => $data['first_login'],
                    'account' => [
                        'id' => $data['customer_account']->id,
                        'username' => $data['customer_account']->username,
                        'status' => $data['customer_account']->status,
                        'customer' => $data['customer_account']->customer,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'Email pemulihan wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $email = strtolower(trim($request->input('email')));

        // 1. Internal users cannot reset via mobile/email
        if (User::where('email', $email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Reset password akun staf internal hanya dapat dilakukan oleh Administrator.',
            ], 422);
        }

        // 2. Check customer account
        $customer = Customer::where('email', $email)->first();

        if ($customer && $customer->customerAccount) {
            $account = $customer->customerAccount;
            if ($account->status === 'active' && $customer->email_verified_at) {
                Password::broker('customer_accounts')->sendResetLink(['email' => $email]);
            }
        }

        // Generic response to prevent email enumeration
        return response()->json([
            'success' => true,
            'message' => 'Jika email terdaftar dan sudah diverifikasi, tautan atur ulang password telah dikirim ke email Anda. Silakan periksa kotak masuk atau spam.',
        ], 200);
    }

    /**
     * Get authenticated customer profile.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customerAccount->load(['customer.bankAccount']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customerAccount->id,
                'username' => $customerAccount->username,
                'first_login' => (bool) $customerAccount->first_login,
                'status' => $customerAccount->status,
                'customer' => $customerAccount->customer,
            ],
        ], 200);
    }

    /**
     * Mobile logout endpoint.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $this->authService->logoutApi($customerAccount);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil keluar dari akun.',
        ], 200);
    }

    /**
     * Change customer password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            /** @var CustomerAccount $customerAccount */
            $customerAccount = $request->user();
            $this->authService->changePassword(
                $customerAccount,
                $request->input('current_password'),
                $request->input('new_password')
            );

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diperbarui.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Change customer PIN.
     */
    public function changePin(ChangePinRequest $request): JsonResponse
    {
        try {
            /** @var CustomerAccount $customerAccount */
            $customerAccount = $request->user();
            $this->authService->changePin(
                $customerAccount,
                $request->input('current_pin'),
                $request->input('new_pin')
            );

            return response()->json([
                'success' => true,
                'message' => 'PIN berhasil diperbarui.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reset customer PIN using account password verification (Lupa PIN).
     */
    public function resetPinWithPassword(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'password' => ['required', 'string'],
            'new_pin' => ['required', 'string', 'digits:6', 'confirmed'],
        ], [
            'password.required' => 'Password akun wajib dimasukkan.',
            'new_pin.required' => 'PIN baru 6 digit wajib dimasukkan.',
            'new_pin.digits' => 'PIN baru harus terdiri dari 6 digit angka.',
            'new_pin.confirmed' => 'Konfirmasi PIN baru tidak cocok.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            /** @var CustomerAccount $customerAccount */
            $customerAccount = $request->user();
            $this->authService->resetPinWithPassword(
                $customerAccount,
                $request->input('password'),
                $request->input('new_pin')
            );

            return response()->json([
                'success' => true,
                'message' => 'PIN transaksi berhasil diperbarui menggunakan verifikasi password akun.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get email verification status.
     */
    public function getEmailStatus(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customer = $customerAccount->customer;

        return response()->json([
            'success' => true,
            'data' => [
                'email' => $customer->email,
                'is_verified' => !is_null($customer->email_verified_at),
                'verified_at' => $customer->email_verified_at?->toIso8601String(),
                'pending_email' => $customer->pending_email,
            ],
        ], 200);
    }

    /**
     * Request email change / verification.
     */
    public function updateEmail(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customer = $customerAccount->customer;

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                \Illuminate\Validation\Rule::unique('customers', 'email')->ignore($customer->id),
                \Illuminate\Validation\Rule::unique('customers', 'pending_email')->ignore($customer->id),
            ],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh nasabah lain.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $newEmail = strtolower(trim($request->input('email')));

        if ($customer->email && strtolower($customer->email) === $newEmail && $customer->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat email ini sudah menjadi email pemulihan aktif Anda.',
            ], 422);
        }

        $customer->update(['pending_email' => $newEmail]);

        try {
            $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'customer.email.verify',
                now()->addMinutes(60),
                ['customer' => $customer->id, 'email' => $newEmail]
            );

            \Illuminate\Support\Facades\Notification::route('mail', $newEmail)
                ->notify(new \App\Notifications\VerifyCustomerEmailNotification($verificationUrl, $customer->name));
        } catch (\Exception $e) {
            // In dev environment where mail server might not be active
        }

        return response()->json([
            'success' => true,
            'message' => "Tautan verifikasi telah dikirim ke {$newEmail}. Silakan periksa inbox email Anda untuk mengaktifkannya.",
            'data' => [
                'pending_email' => $newEmail,
            ],
        ], 200);
    }

    /**
     * Resend verification email.
     */
    public function resendEmailVerification(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customer = $customerAccount->customer;

        if (!$customer->pending_email) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada permintaan verifikasi email yang sedang tertunda.',
            ], 400);
        }

        try {
            $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'customer.email.verify',
                now()->addMinutes(60),
                ['customer' => $customer->id, 'email' => $customer->pending_email]
            );

            \Illuminate\Support\Facades\Notification::route('mail', $customer->pending_email)
                ->notify(new \App\Notifications\VerifyCustomerEmailNotification($verificationUrl, $customer->name));
        } catch (\Exception $e) {
            // Dev error handling
        }

        return response()->json([
            'success' => true,
            'message' => "Tautan verifikasi baru telah dikirimkan ulang ke {$customer->pending_email}.",
        ], 200);
    }

    /**
     * Cancel pending email change.
     */
    public function cancelPendingEmail(Request $request): JsonResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = $request->user();
        $customerAccount->customer->update(['pending_email' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan verifikasi email telah dibatalkan.',
        ], 200);
    }
}
