<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Members with status rejected/blocked only see the status screen.
 * Pending members may log in but are limited to a read-only feed (see PENDING_ALLOWED).
 */
class EnsureActive
{
    /** Route names a not-yet-approved member may reach. Everything else is blocked until approval. */
    public const PENDING_ALLOWED = [
        'home',
        'posts.show',
        'profile.show',
        'profile.edit',
        'profile.update',
        'profile.destroy',
        'notifications.index',
        'notifications.read',
        'notifications.read-all',
        'account.status',
        'locale.switch',
        'news.show',
        'push.subscribe',
        'push.unsubscribe',
    ];

    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if (! $u || $u->isActive() || $u->isAdmin()) {
            return $next($request);
        }

        if ($u->isPending()) {
            if (in_array($request->route()?->getName(), self::PENDING_ALLOWED, true)) {
                return $next($request);
            }
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'एडमिन की मंज़ूरी बाकी है'], 403);
            }

            return redirect()->route('home')->with('pending_notice', 'एडमिन की मंज़ूरी बाकी है');
        }

        return redirect()->route('account.status');
    }
}
