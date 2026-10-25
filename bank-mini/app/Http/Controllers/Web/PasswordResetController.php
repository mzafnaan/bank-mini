<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ForceChangePasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\Customer;
use App\Models\CustomerAccount;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Show the forgot password form.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle the forgot password form submission.
     * Sends reset link to email for both users and customer_accounts.
     * Uses generic message to avoid email enumeration.
     */
    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $email = $request->validated('email');

        // 1. Try internal user first
        $user = User::where('email', $email)->first();
        if ($user) {
            return $this->sendResetLinkForUser($user, $email);
        }

        // 2. Try customer account (email is on customers table)
        $customer = Customer::where('email', $email)->first();
        if ($customer && $customer->customerAccount) {
            return $this->sendResetLinkForCustomer($customer, $email);
        }

        // Generic message - don't reveal if email exists or not
        return back()->with('status', 'Jika email terdaftar dan sudah diverifikasi, kami akan mengirimkan tautan untuk mengatur ulang password Anda.');
    }

    /**
     * Send reset link for internal user.
     */
    private function sendResetLinkForUser(User $user, string $email): RedirectResponse
    {
        // Check if account is active
        if ($user->status !== 'active') {
            return back()->with('status', 'Jika email terdaftar dan sudah diverifikasi, kami akan mengirimkan tautan untuk mengatur ulang password Anda.');
        }

        // Check if email is verified
        if (! $user->email_verified_at) {
            return back()->with('status', 'Jika email terdaftar dan sudah diverifikasi, kami akan mengirimkan tautan untuk mengatur ulang password Anda.');
        }

        $status = Password::broker('users')->sendResetLink(
            ['email' => $email]
        );

        // Always show generic message
        return back()->with('status', 'Jika email terdaftar dan sudah diverifikasi, kami akan mengirimkan tautan untuk mengatur ulang password Anda.');
    }

    /**
     * Send reset link for customer account.
     */
    private function sendResetLinkForCustomer(Customer $customer, string $email): RedirectResponse
    {
        $customerAccount = $customer->customerAccount;

        // Check if account is active
        if (! $customerAccount || $customerAccount->status !== 'active') {
            return back()->with('status', 'Jika email terdaftar dan sudah diverifikasi, kami akan mengirimkan tautan untuk mengatur ulang password Anda.');
        }

        // Check if email is verified
        if (! $customer->email_verified_at) {
            return back()->with('status', 'Jika email terdaftar dan sudah diverifikasi, kami akan mengirimkan tautan untuk mengatur ulang password Anda.');
        }

        $status = Password::broker('customer_accounts')->sendResetLink(
            ['email' => $email]
        );

        return back()->with('status', 'Jika email terdaftar dan sudah diverifikasi, kami akan mengirimkan tautan untuk mengatur ulang password Anda.');
    }

    /**
     * Show the password reset form (from email link).
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Handle the password reset form submission.
     * Tries users broker first, then customer_accounts broker.
     */
    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $email = $request->validated('email');

        // 1. Try internal user reset
        $user = User::where('email', $email)->first();
        if ($user) {
            return $this->resetUserPassword($request);
        }

        // 2. Try customer account reset
        $customer = Customer::where('email', $email)->first();
        if ($customer && $customer->customerAccount) {
            return $this->resetCustomerPassword($request, $customer);
        }

        return back()->withErrors(['email' => 'Token reset password tidak valid atau sudah kedaluwarsa.']);
    }

    /**
     * Reset internal user password via Password Broker.
     */
    private function resetUserPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $this->authService->resetPassword($user, $password);
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');
        }

        return back()->withErrors(['email' => $this->translateResetStatus($status)]);
    }

    /**
     * Reset customer account password via Password Broker.
     */
    private function resetCustomerPassword(ResetPasswordRequest $request, Customer $customer): RedirectResponse
    {
        $customerAccount = $customer->customerAccount;

        // Check if account is active
        if (! $customerAccount || $customerAccount->status !== 'active') {
            return back()->withErrors(['email' => 'Akun tidak aktif. Silakan hubungi Administrator.']);
        }

        $status = Password::broker('customer_accounts')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (CustomerAccount $account, string $password) {
                $this->authService->resetPassword($account, $password);
                event(new PasswordReset($account));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');
        }

        return back()->withErrors(['email' => $this->translateResetStatus($status)]);
    }

    /**
     * Translate Laravel's password reset status to Indonesian.
     */
    private function translateResetStatus(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'Token reset password tidak valid atau sudah kedaluwarsa.',
            Password::INVALID_USER => 'Email tidak ditemukan dalam sistem kami.',
            Password::RESET_THROTTLED => 'Terlalu banyak permintaan. Silakan tunggu sebelum mencoba kembali.',
            default => 'Terjadi kesalahan. Silakan coba lagi.',
        };
    }

    /**
     * Show the force change password form for first_login customers.
     */
    public function showForceChangeForm(): View|RedirectResponse
    {
        $customerAccount = auth()->guard('customer_web')->user();

        if (! $customerAccount instanceof CustomerAccount || ! $customerAccount->first_login) {
            return redirect()->route('customer.dashboard');
        }

        return view('auth.force-change-password');
    }

    /**
     * Handle forced password change for first_login customers.
     */
    public function forceChangePassword(ForceChangePasswordRequest $request): RedirectResponse
    {
        /** @var CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();

        if (! $customerAccount instanceof CustomerAccount || ! $customerAccount->first_login) {
            return redirect()->route('customer.dashboard');
        }

        $customerAccount->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->validated('new_password')),
            'first_login' => false,
        ]);

        return redirect()->route('customer.dashboard')->with('success', 'Password berhasil diubah. Selamat datang di Bank Mini Sekolah!');
    }
}
