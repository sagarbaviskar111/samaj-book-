<?php

namespace App\Services;

use App\Models\Biodata;
use App\Models\User;
use App\Notifications\GeneralNotification;

/**
 * "Area alert": when a biodata is approved, tell active members who have an approved
 * biodata of the opposite gender in the same district. Call from the admin approval flow.
 */
class NewBiodataAlert
{
    public const MAX_RECIPIENTS = 200;

    public function handle(Biodata $b): int
    {
        if (! $b->district) {
            return 0;
        }
        $oppositeGender = $b->gender === 'male' ? 'female' : 'male';

        $userIds = Biodata::approved()
            ->where('gender', $oppositeGender)
            ->where('district', $b->district)
            ->where('user_id', '!=', $b->user_id)
            ->distinct()
            ->limit(self::MAX_RECIPIENTS)
            ->pluck('user_id');

        $users = User::whereIn('id', $userIds)->where('status', 'active')->get();
        $first = explode(' ', trim($b->name))[0] ?? $b->name;
        $age = $b->dob?->age;

        foreach ($users as $user) {
            $user->notify(new GeneralNotification(
                'new_biodata_area',
                __('Aapke ilake me nayi profile'),
                trim($first.($age ? ', '.__(':age saal', ['age' => $age]) : '').' — '.$b->district),
                route('biodata.index', ['for' => $b->gender === 'male' ? 'groom' : 'bride', 'district' => $b->district]),
            ));
        }

        return $users->count();
    }
}
