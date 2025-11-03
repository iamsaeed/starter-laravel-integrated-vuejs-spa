<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessUserPanel
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

        // Check if user has the 'user' role
        if (! $request->user()->isUser()) {
            // For web routes, redirect to admin dashboard if they can access it
            if (! $request->expectsJson()) {
                if ($request->user()->canAccessAdminPanel()) {
                    return redirect()->route('admin');
                }

                return redirect()->route('login');
            }

            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
