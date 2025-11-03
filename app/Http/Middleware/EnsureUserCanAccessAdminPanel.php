<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessAdminPanel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (! $request->user()) {
            // For web routes, redirect to login
            if (! $request->expectsJson()) {
                return redirect()->route('login');
            }

            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check both admin role AND whitelist
        if (! $request->user()->canAccessAdminPanel()) {
            // For web routes, redirect to user dashboard if they have user role
            if (! $request->expectsJson()) {
                if ($request->user()->isUser()) {
                    return redirect()->route('user');
                }

                return redirect()->route('login');
            }

            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
