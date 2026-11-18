<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Container\Attributes\Bind;

/** Hook for OTP verification; swap the implementation later. */
#[Bind(NullPhoneVerifier::class)]
interface PhoneVerifier
{
    /** Start verification (e.g. send OTP). Return true if a challenge was issued. */
    public function send(User $user): bool;

    /** Check a submitted code. */
    public function verify(User $user, string $code): bool;
}
