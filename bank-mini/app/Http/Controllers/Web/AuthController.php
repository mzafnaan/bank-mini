<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}
    public function showLoginForm(): View|RedirectResponse
    {
        if (auth()->guard('web')->check()) {
            return $this->redirectBasedOnRole(auth()->guard('web')->user()->role);
        }
        if (auth()->guard('customer_web')->check()) {
            return redirect()->route('customer.dashboard');
        }
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $result = $this->authService->loginWeb($request->validated());

            if ($result['type'] === 'staff') {
                return $this->redirectBasedOnRole($result['user']->role)
                    ->with('success', 'Selamat datang kembali, '.$result['user']->name);
            }

            return redirect()->route('customer.dashboard')
                ->with('success', 'Selamat datang kembali!');
        } catch (\Exception $e) {
            return back()->withInput($request->only('username'))
                ->withErrors(['username' => $e->getMessage()]);
        }
    }

    public function logout(): RedirectResponse
    {
        $this->authService->logoutWeb();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    /**
     * Update authenticated staff user password (FR-004).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->guard('web')->user();
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->input('password')),
        ]);

        return back()->with('success', 'Password akun Anda berhasil diperbarui!');
    }

    protected function redirectBasedOnRole(string $role): RedirectResponse
    {
        return match ($role) {
            'administrator' => redirect()->route('admin.dashboard'),
            'teller' => redirect()->route('teller.dashboard'),
            'supervisor' => redirect()->route('supervisor.dashboard'),
            default => redirect()->route('login'),
        };
    }
}