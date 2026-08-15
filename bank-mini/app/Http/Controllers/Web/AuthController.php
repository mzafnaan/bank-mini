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

    /**
     * Display the login view.
     */
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

    /**
     * Handle an incoming authentication request.
     */
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

    /**
     * Destroy an authenticated session.
     */
    public function logout(): RedirectResponse
    {
        $this->authService->logoutWeb();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    /**
     * Redirect user based on staff role.
     */
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
