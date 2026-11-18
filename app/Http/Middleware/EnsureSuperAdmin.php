<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Superadmin only (role superadmin, active); everyone else gets 403. */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        abort_unless($u?->isSuperAdmin() && $u->status === 'active', 403);

        return $next($request);
    }
}
