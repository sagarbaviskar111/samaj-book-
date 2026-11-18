<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $key = 'login:'.$request->input('phone').'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'phone' => __('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        $user = User::where('phone', $request->input('phone'))->first();

        if (! $user || ! Auth::validate(['phone' => $user->phone, 'password' => $request->input('password')])) {
            RateLimiter::hit($key, 60);
            event(new Failed('web', $user, ['phone' => $request->input('phone')])); // -> login_logs
            throw ValidationException::withMessages(['phone' => __('auth.failed')]);
        }

        if ($user->status === 'blocked') {
            event(new Failed('web', $user, ['phone' => $request->input('phone')]));
            throw ValidationException::withMessages(['phone' => __('auth.blocked')]);
        }

        RateLimiter::clear($key);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->forceFill(['last_seen_at' => now()])->save();
        if ($user->language) {
            session(['locale' => $user->language]);
        }

        if ($user->isSuperAdmin() && Route::has('superadmin.dashboard')) {
            return redirect()->intended(route('superadmin.dashboard'));
        }

        if ($user->isAdmin()) {
            return redirect()->intended(Route::has('admin.dashboard') ? route('admin.dashboard') : '/');
        }

        if ($user->isPending()) {
            return redirect()->route('home');
        }

        return redirect()->intended(Route::has('home') ? route('home') : '/');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
