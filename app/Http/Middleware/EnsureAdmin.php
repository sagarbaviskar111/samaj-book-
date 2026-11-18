<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        abort_unless($u?->isAdmin(), 403);
        // a blocked/deactivated admin of any tier loses access immediately
        abort_if($u->status !== 'active', 403);

        return $next($request);
    }
}
