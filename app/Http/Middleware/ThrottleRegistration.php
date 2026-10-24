<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limits Fortify's registration endpoint, which has no limiter of its own.
 */
class ThrottleRegistration
{
    private const MAX_ATTEMPTS = 5;

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('post') || ! $request->routeIs('register.store')) {
            return $next($request);
        }

        $key = 'register|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            abort(429, 'Too many registration attempts. Please try again later.');
        }

        RateLimiter::hit($key, 3600);

        return $next($request);
    }
}
