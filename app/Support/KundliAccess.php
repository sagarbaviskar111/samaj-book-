<?php

namespace App\Support;

use App\Models\Biodata;
use App\Models\Interest;
use App\Models\User;

/**
 * Who may see rashi/nakshatra and the Gun Milan result.
 * Rule: only after an ACCEPTED interest between the two profiles, and only for the two parties
 * (sender / receiver users, i.e. including on-behalf relatives) or admins. Owners always see their own values.
 */
class KundliAccess
{
    /** Accepted interest between two biodatas (either direction), newest first. */
    public static function acceptedBetween(Biodata $a, Biodata $b): ?Interest
    {
        return Interest::where('status', 'accepted')->where(function ($q) use ($a, $b) {
            $q->where(fn ($w) => $w->where('from_biodata_id', $a->id)->where('to_biodata_id', $b->id))
              ->orWhere(fn ($w) => $w->where('from_biodata_id', $b->id)->where('to_biodata_id', $a->id));
        })->latest('id')->first();
    }

    public static function canSeeKundli(?User $viewer, Biodata $a, Biodata $b): bool
    {
        if (! $viewer) {
            return false;
        }
        if ($viewer->isAdmin()) {
            return true;
        }
        $interest = static::acceptedBetween($a, $b);
        if (! $interest) {
            return false;
        }

        return in_array($viewer->id, [(int) $interest->from_user_id, (int) $interest->to_user_id], true)
            || in_array($viewer->id, [(int) $a->user_id, (int) $b->user_id], true);
    }

    public static function canOpenInterest(?User $viewer, Interest $interest): bool
    {
        if (! $viewer) {
            return false;
        }
        if ($viewer->isAdmin()) {
            return true;
        }
        if ($interest->status !== 'accepted') {
            return false;
        }

        return in_array($viewer->id, [(int) $interest->from_user_id, (int) $interest->to_user_id], true);
    }

    /** May the viewer see $target's rashi/nakshatra values (owner, admin, or accepted interest with it)? */
    public static function canSeeProfileValues(?User $viewer, Biodata $target): bool
    {
        return BiodataAccess::isPrivileged($viewer, $target) || BiodataAccess::hasAcceptedInterest($viewer, $target);
    }

    /** The accepted interest linking one of the viewer's biodatas with $target (for the result link). */
    public static function interestFor(?User $viewer, Biodata $target): ?Interest
    {
        if (! $viewer) {
            return null;
        }

        return Interest::where('status', 'accepted')->where(function ($q) use ($viewer, $target) {
            $q->where(fn ($w) => $w->where('from_user_id', $viewer->id)->where('to_biodata_id', $target->id))
              ->orWhere(fn ($w) => $w->where('to_user_id', $viewer->id)->where('from_biodata_id', $target->id));
        })->latest('id')->first();
    }
}
