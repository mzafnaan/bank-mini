<?php

namespace App\Services;

use App\Models\CustomerAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Authenticate user for Web (Session).
     * Supports both User (staff) and CustomerAccount (student/customer).
     *
     * @param  array{username: string, password: string}  $credentials
     * @return array{user: User|CustomerAccount, type: string}
     */
    public function loginWeb(array $credentials): array
    {
        // 1. Try internal staff user login
        if (Auth::guard('web')->attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $user = Auth::guard('web')->user();

            if ($user->status !== 'active') {
                Auth::guard('web')->logout();
                throw new \Exception('Akun Anda tidak aktif. Silakan hubungi Administrator.');
            }

            return ['user' => $user, 'type' => 'staff'];
        }

        // 2. Try customer account login
        if (Auth::guard('customer_web')->attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $customer = Auth::guard('customer_web')->user();

            if ($customer->status !== 'active') {
                Auth::guard('customer_web')->logout();
                throw new \Exception('Akun nasabah Anda tidak aktif.');
            }

            return ['user' => $customer, 'type' => 'customer'];
        }

        throw new \Exception('Username atau password salah.');
    }

    /**
     * Authenticate customer for Mobile API (Sanctum Token).
     *
     * @param  array{username: string, password: string}  $credentials
     */
    public function loginApi(array $credentials): array
    {
        $account = CustomerAccount::where('username', $credentials['username'])->first();

        if (! $account || ! Hash::check($credentials['password'], $account->password)) {
            throw new \Exception('Username atau password salah.');
        }

        if ($account->status !== 'active') {
            throw new \Exception('Akun nasabah Anda tidak aktif.');
        }

        $token = $account->createToken('mobile-app')->plainTextToken;

        return [
            'token' => $token,
            'customer_account' => $account->load(['customer.bankAccount']),
            'first_login' => (bool) $account->first_login,
        ];
    }

    /**
     * Logout web sessions.
     */
    public function logoutWeb(): void
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        if (Auth::guard('customer_web')->check()) {
            Auth::guard('customer_web')->logout();
        }

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    /**
     * Logout mobile API token.
     */
    public function logoutApi(CustomerAccount $customerAccount): void
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token = $customerAccount->currentAccessToken();

        if ($token) {
            $token->delete();
        }
    }

    /**
     * Change password for User or CustomerAccount.
     */
    public function changePassword(User|CustomerAccount $user, string $currentPassword, string $newPassword): bool
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Password saat ini tidak sesuai.');
        }

        $data = [
            'password' => Hash::make($newPassword),
        ];

        if ($user instanceof CustomerAccount) {
            $data['first_login'] = false;
        }

        return $user->update($data);
    }

    /**
     * Change PIN for CustomerAccount.
     */
    public function changePin(CustomerAccount $customerAccount, string $currentPin, string $newPin): bool
    {
        if (! Hash::check($currentPin, $customerAccount->pin)) {
            throw new \Exception('PIN saat ini tidak sesuai.');
        }

        return $customerAccount->update([
            'pin' => Hash::make($newPin),
        ]);
    }
}
