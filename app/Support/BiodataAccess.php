<?php

namespace App\Support;

use App\Models\Biodata;
use App\Models\Interest;
use App\Models\User;

/**
 * Central authorization for biodata (used instead of a registered policy).
 */
class BiodataAccess
{
    /** @var array<string,bool> per-request memo */
    protected static array $memo = [];

    public static function flush(): void
    {
        static::$memo = [];
    }

    public static function isOwner(?User $user, Biodata $b): bool
    {
        return $user && (int) $b->user_id === (int) $user->id;
    }

    public static function isAdmin(?User $user): bool
    {
        return (bool) $user?->isAdmin();
    }

    /** Owner or admin: full control/visibility. */
    public static function isPrivileged(?User $user, Biodata $b): bool
    {
        return static::isOwner($user, $b) || static::isAdmin($user);
    }

    public static function canView(?User $user, Biodata $b): bool
    {
        if (! $user) {
            return false;
        }
        return static::isPrivileged($user, $b) || $b->status === 'approved';
    }

    public static function canManage(?User $user, Biodata $b): bool
    {
        return static::isOwner($user, $b);
    }

    /** Accepted interest in either direction between any of the viewer's biodatas and $b. */
    public static function hasAcceptedInterest(?User $user, Biodata $b): bool
    {
        if (! $user) {
            return false;
        }
        $key = $user->id.':'.$b->id;
        return static::$memo[$key] ??= (function () use ($user, $b) {
            $mine = Biodata::where('user_id', $user->id)->pluck('id');
            if ($mine->isEmpty()) {
                return false;
            }
            return Interest::where('status', 'accepted')
                ->where(function ($q) use ($mine, $b) {
                    $q->where(fn ($w) => $w->whereIn('from_biodata_id', $mine)->where('to_biodata_id', $b->id))
                      ->orWhere(fn ($w) => $w->whereIn('to_biodata_id', $mine)->where('from_biodata_id', $b->id));
                })->exists();
        })();
    }

    public static function canSeeContact(?User $user, Biodata $b): bool
    {
        return static::isPrivileged($user, $b) || static::hasAcceptedInterest($user, $b);
    }

    public static function canSeeIncome(?User $user, Biodata $b): bool
    {
        return ! $b->income_private || static::canSeeContact($user, $b);
    }

    public static function canDownloadPdf(?User $user, Biodata $b): bool
    {
        return static::canSeeContact($user, $b);
    }

    /** @return 'clear'|'blur'|'placeholder'|'none' */
    public static function photoMode(?User $user, Biodata $b): string
    {
        if (! static::canView($user, $b)) {
            return 'none';
        }
        if (static::canSeeContact($user, $b)) {
            return 'clear';
        }
        return match ($b->photo_privacy) {
            'blur_until_accepted' => 'blur',
            'accepted_only' => 'placeholder',
            default => 'clear',
        };
    }
}
