<?php

namespace App\Support;

use App\Models\Biodata;
use App\Models\LookingForProfile;
use App\Models\User;

/**
 * Single source of truth for how much of other people's biodata a member may see.
 *   full      owns at least one non-draft biodata (or is admin)
 *   view_only (retired: looking-for no longer unlocks anything)
 *   lite      no own submitted biodata: nothing of anyone's biodata is shown, only counts + districts
 * Memoized per request (stored on the request so tests/long-lived workers never see stale values).
 */
class AccessLevel
{
    public const FULL = 'full';
    public const VIEW_ONLY = 'view_only';
    public const LITE = 'lite';

    public static function for(?User $user): string
    {
        if (! $user) {
            return self::LITE;
        }
        if ($user->isAdmin()) {
            return self::FULL;
        }
        $bag = request()->attributes;
        $key = 'access_level.'.$user->id;
        if ($bag->has($key)) {
            return $bag->get($key);
        }
        $level = match (true) {
            Biodata::where('user_id', $user->id)->where('status', '!=', 'draft')->exists() => self::FULL,
            default => self::LITE,
        };
        $bag->set($key, $level);

        return $level;
    }

    public static function isLite(?User $user): bool
    {
        return static::for($user) === self::LITE;
    }

    public static function canSend(?User $user): bool
    {
        return static::for($user) === self::FULL;
    }

    public static function lookingFor(?User $user): ?LookingForProfile
    {
        return $user ? LookingForProfile::where('user_id', $user->id)->first() : null;
    }
}
