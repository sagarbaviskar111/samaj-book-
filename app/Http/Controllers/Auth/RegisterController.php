<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Auth\PhoneVerifier;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, PhoneVerifier $verifier)
    {
        $data = $request->safe()->except(['consent', 'password_confirmation']);

        $user = new User($data);
        $user->role = 'user';
        $user->status = 'pending';
        $user->language = session('locale', 'hi');
        $user->last_seen_at = now();
        $user->save();

        // OTP hook: no-op by default; a real verifier can issue a challenge here.
        $verifier->send($user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}
