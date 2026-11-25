<?php

namespace App\Support;

use App\Models\User;

/**
 * LOCAL DEVELOPMENT ONLY. One-click login accounts shown on the login page and in
 * the floating "DEV" switcher. Every entry point checks app()->environment('local').
 *
 * Demo data: members 9000000001-20 = LADKA biodata owners, 9000000021-40 = LADKI biodata owners.
 */
class DevLogin
{
    public static function enabled(): bool
    {
        return app()->environment('local') && config('app.debug');
    }

    /** @return array<string, array<int, array{phone:string,label:string,hint:string,name:?string}>> */
    public static function groups(): array
    {
        $ladka = array_map(fn ($i) => ['90000000'.str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'Ladka', 'Biodata + full access'], range(1, 5));
        $ladki = array_map(fn ($i) => ['90000000'.(20 + $i), 'Ladki', 'Biodata + full access'], range(1, 5));

        $groups = [
            'Admin' => [
                ['9999999999', 'Admin', 'Members, biodata, events, news'],
                ['9888888801', 'Sub-admin Nashik', 'Sirf Nashik area'],
                ['9888888802', 'Sub-admin Pune', 'Sirf Pune area'],
            ],
            'Ladka (biodata hai)' => $ladka,
            'Ladki (biodata hai)' => $ladki,
            'Naye members (biodata nahi, lite view)' => [
                ['9000000045', 'Ladka (naya)', 'Account bana, biodata nahi'],
                ['9000000044', 'Ladki (naya)', 'Account bana, biodata nahi'],
                ['9000000046', 'Looking-for (view only)', 'Kisi aur ke liye dekh raha'],
            ],
            'Special cases' => [
                ['9000000041', 'Pending member', 'Admin approval baaki'],
                ['9000000001', 'Cousin ke liye request', 'Rahul: cousin ka biodata bhi hai'],
            ],
        ];

        $names = User::whereIn('phone', collect($groups)->flatten(1)->pluck(0))->pluck('name', 'phone');

        return collect($groups)->map(fn ($rows) => collect($rows)
            ->filter(fn ($r) => $names->has($r[0]))        // hide accounts not seeded in this DB
            ->map(fn ($r) => ['phone' => $r[0], 'label' => $r[1], 'hint' => $r[2], 'name' => $names[$r[0]]])
            ->values()->all()
        )->filter()->all();
    }
}
