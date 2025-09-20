<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerifiedOrAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If not authenticated, let the regular auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Allow admins regardless of verification
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        // For non-admins, require verified email
        if (!$user || !$user->hasVerifiedEmail()) {
            // If the request expects JSON, return 403 to keep API consistent
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your email address is not verified.'
                ], 403);
            }

            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
