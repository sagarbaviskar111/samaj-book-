<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Route;

class AdminNotifier
{
    public static function routeOrNull(string $name, mixed $params = []): ?string
    {
        return Route::has($name) ? route($name, $params) : null;
    }

    public static function to(User $user, string $type, string $title, ?string $body = null, ?string $url = null): void
    {
        $user->notify(new GeneralNotification($type, $title, $body, $url));
    }

    /** Notify all active members in chunks (keeps memory flat). Returns count. */
    public static function broadcast(string $type, string $title, ?string $body = null, ?string $url = null, ?string $area = null): int
    {
        $count = 0;
        $query = User::query()->where('status', 'active')->where('role', 'user');
        if ($area) {
            AdminScope::inArea($query, $area);
        }
        $query->chunkById(200, function ($users) use ($type, $title, $body, $url, &$count) {
                foreach ($users as $u) {
                    $u->notify(new GeneralNotification($type, $title, $body, $url));
                    $count++;
                }
            });

        return $count;
    }
}
