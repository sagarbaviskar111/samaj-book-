<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;

/** Per-user notification opt-outs, stored as JSON in Setting `notif_pref_{userId}`. */
class NotificationPrefs
{
    public const GROUPS = [
        'like' => 'Likes on my posts',
        'comment' => 'Comments on my posts',
        'interest' => 'Rishta requests and replies',
        'new_biodata_area' => 'New biodata in my area',
        'news' => 'News, posters and events',
        'system' => 'Account and other updates',
    ];

    public static function groupOf(?string $type): string
    {
        return match (true) {
            $type === 'like' => 'like',
            $type === 'comment' => 'comment',
            str_starts_with((string) $type, 'interest_') => 'interest',
            $type === 'new_biodata_area' => 'new_biodata_area',
            in_array($type, ['news', 'event'], true) => 'news',
            default => 'system',
        };
    }

    /** @return array<string,bool> */
    public static function for(User|int $user): array
    {
        $id = $user instanceof User ? $user->id : $user;
        $saved = json_decode((string) Setting::get('notif_pref_'.$id, '{}'), true) ?: [];
        $out = [];
        foreach (self::GROUPS as $k => $_) {
            $out[$k] = (bool) ($saved[$k] ?? true);
        }

        return $out;
    }

    public static function save(User $user, array $enabled): void
    {
        $out = [];
        foreach (self::GROUPS as $k => $_) {
            $out[$k] = (bool) ($enabled[$k] ?? false);
        }
        Setting::put('notif_pref_'.$user->id, json_encode($out));
    }

    public static function allows(User $user, ?string $type): bool
    {
        return self::for($user)[self::groupOf($type)] ?? true;
    }
}
