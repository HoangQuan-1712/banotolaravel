<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !(auth()->user()->is_admin ?? false)) {
            abort(403, 'Chỉ admin mới được truy cập.');
        }
        return $next($request);
    }
}
