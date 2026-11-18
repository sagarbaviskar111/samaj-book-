<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\DevLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** One-click login for local development. Returns 404 anywhere except APP_ENV=local + APP_DEBUG=true on loopback. */
class DevLoginController extends Controller
{
    public function login(Request $request, string $phone)
    {
        abort_unless(DevLogin::enabled() && in_array($request->ip(), ['127.0.0.1', '::1'], true), 404);

        $user = User::where('phone', $phone)->firstOrFail();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill(['last_seen_at' => now()])->save();

        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route(($user->isActive() || $user->isPending()) ? 'home' : 'account.status');
    }
}
