<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QueryTokenAuth
{
    /**
     * Handle an incoming request.
     * Allows passing API Bearer token in query string parameter (?token=...)
     * particularly useful for browser file downloads and print views.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->filled('token') && ! $request->bearerToken()) {
            $request->headers->set('Authorization', 'Bearer ' . $request->query('token'));
        }

        return $next($request);
    }
}

