<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::guard('web')->user()
            ?? Auth::guard('customer_web')->user()
            ?? Auth::guard('sanctum')->user()
            ?? $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userRole = match (true) {
            $user instanceof \App\Models\User => $user->role,
            $user instanceof \App\Models\CustomerAccount => 'customer',
            default => null,
        };

        if ($userRole === null || ! in_array($userRole, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki hak akses.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Anda tidak memiliki hak akses.');
        }

        return $next($request);
    }
}
