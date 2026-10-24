<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the student portal and checkout to student accounts.
 */
class EnsureStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isStudent()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'The student portal is only available to student accounts.');
        }

        return $next($request);
    }
}
