<?php

namespace App\Services\Auth;

use App\Models\User;

/** No-op default: no OTP is sent and nothing is required. */
class NullPhoneVerifier implements PhoneVerifier
{
    public function send(User $user): bool
    {
        return false;
    }

    public function verify(User $user, string $code): bool
    {
        return true;
    }
}
