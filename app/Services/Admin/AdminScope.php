<?php

namespace App\Services\Admin;

use App\Models\Biodata;
use App\Models\Comment;
use App\Models\Interest;
use App\Models\Post;
use App\Models\User;

/**
 * Single place that decides which data an admin may see.
 * The main admin (role admin) sees everything (area() === null).
 * A sub-admin is limited to the single area (district name) in users.admin_area.
 * ALL admin list/lookup queries must pass through these helpers.
 */
class AdminScope
{
    /** Matches nothing: used when a sub-admin has no area assigned. */
    public const NONE = "\0none";

    /** Area restriction for the current admin; null = all areas. */
    public static function area(?User $admin = null): ?string
    {
        $admin ??= auth()->user();
        if ($admin && $admin->role === 'subadmin') {
            return filled($admin->admin_area) ? $admin->admin_area : self::NONE;
        }

        return null;
    }

    public static function isRestricted(): bool
    {
        return static::area() !== null;
    }

    /** Members of an area: users.district or users.city equals the area. */
    public static function inArea($query, string $area)
    {
        return $query->where(fn ($q) => $q->where('district', $area)->orWhere('city', $area));
    }

    /** Regular members only (never admins), limited to the admin's area. */
    public static function users($query)
    {
        $query->where('role', 'user');
        if ($area = static::area()) {
            static::inArea($query, $area);
        }

        return $query;
    }

    public static function biodatas($query)
    {
        if ($area = static::area()) {
            $query->where('district', $area);
        }

        return $query;
    }

    /** A sub-admin only manages items of their own area. */
    public static function news($query)
    {
        if ($area = static::area()) {
            $query->where('area', $area);
        }

        return $query;
    }

    /** Events: a sub-admin only manages events of their own area. */
    public static function events($query)
    {
        if ($area = static::area()) {
            $query->where('area', $area);
        }

        return $query;
    }

    /** Reports whose target (member / post / biodata / comment / interest) belongs to the area. */
    public static function reports($query)
    {
        $area = static::area();
        if (! $area) {
            return $query;
        }
        $ids = fn () => static::inArea(User::query()->where('role', 'user'), $area)->select('id');

        return $query->where(function ($q) use ($area, $ids) {
            $q->where(fn ($w) => $w->where('target_type', 'user')->whereIn('target_id', $ids()))
                ->orWhere(fn ($w) => $w->where('target_type', 'post')->whereIn('target_id', Post::query()->whereIn('user_id', $ids())->select('id')))
                ->orWhere(fn ($w) => $w->where('target_type', 'comment')->whereIn('target_id', Comment::query()->whereIn('user_id', $ids())->select('id')))
                ->orWhere(fn ($w) => $w->where('target_type', 'biodata')->whereIn('target_id', Biodata::query()->where('district', $area)->select('id')))
                ->orWhere(fn ($w) => $w->where('target_type', 'interest_help')->whereIn('target_id', Interest::query()
                    ->where(fn ($i) => $i->whereIn('from_user_id', $ids())->orWhereIn('to_user_id', $ids()))->select('id')));
        });
    }

    /** Interests where the sender or receiver belongs to the admin's area. */
    public static function interests($query)
    {
        if ($area = static::area()) {
            $ids = fn () => static::inArea(User::query()->where('role', 'user'), $area)->select('id');
            $query->where(fn ($i) => $i->whereIn('from_user_id', $ids())->orWhereIn('to_user_id', $ids()));
        }

        return $query;
    }

    /** Success stories are for the main admin only. */
    public static function stories($query)
    {
        if (static::area()) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
