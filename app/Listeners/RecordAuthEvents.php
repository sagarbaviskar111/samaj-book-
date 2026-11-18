<?php

namespace App\Listeners;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

/** Writes login_logs from Laravel auth events (auto-discovered: handle* methods). Never stores passwords. */
class RecordAuthEvents
{
    public function handleLogin(Login $event): void
    {
        $dev = request()->routeIs('dev.login');
        $this->write($event->user instanceof User ? $event->user : null, $event->user->phone ?? null, $dev ? 'dev_login' : 'login');
    }

    public function handleFailed(Failed $event): void
    {
        $phone = $event->credentials['phone'] ?? ($event->user->phone ?? null);
        $this->write($event->user instanceof User ? $event->user : null, $phone, 'failed');
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user instanceof User) {
            $this->write($event->user, $event->user->phone, 'logout');
        }
    }

    private function write(?User $user, ?string $phone, string $kind): void
    {
        try {
            LoginLog::create([
                'user_id' => $user?->id,
                'phone' => $phone ? mb_substr((string) $phone, 0, 30) : null,
                'event' => $kind,
                'ip' => request()->ip(),
                'user_agent' => mb_substr((string) request()->userAgent(), 0, 255) ?: null,
            ]);
        } catch (\Throwable $e) {
            report($e); // logging must never break login
        }
    }
}
