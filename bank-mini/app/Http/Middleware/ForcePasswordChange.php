<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     * Redirects customer accounts with first_login=true to the forced password change page.
     * Allows logout so the customer can still sign out if needed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customerAccount = Auth::guard('customer_web')->user();

        if ($customerAccount
            && $customerAccount instanceof \App\Models\CustomerAccount
            && $customerAccount->first_login
            && ! $request->routeIs('password.force-change')
            && ! $request->routeIs('password.force-change.update')
            && ! $request->routeIs('logout')
        ) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus mengganti password terlebih dahulu.',
                    'first_login' => true,
                ], 403);
            }

            return redirect()->route('password.force-change')
                ->with('warning', 'Demi keamanan akun, Anda wajib mengganti password sebelum menggunakan layanan.');
        }

        return $next($request);
    }
}
