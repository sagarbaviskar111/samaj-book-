<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Main admin only (role admin); sub-admins get 403. */
class EnsureFullAdmin
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->isFullAdmin(), 403);

        return $next($request);
    }
}
