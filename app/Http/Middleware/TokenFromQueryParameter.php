<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenFromQueryParameter
{
    /**
     * Handle an incoming request.
     *
     * This middleware allows token authentication via query parameter for requests
     * that cannot send custom headers (e.g., EventSource for Server-Sent Events).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If token is provided as query parameter and no Authorization header exists
        if ($request->query('token') && ! $request->header('Authorization')) {
            $request->headers->set('Authorization', 'Bearer '.$request->query('token'));
        }

        return $next($request);
    }
}
