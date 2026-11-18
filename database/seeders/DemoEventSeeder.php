<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoEventSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('phone', '9999999999')->first();

        $make = fn (string $title, array $attrs) => Event::updateOrCreate(
            ['title' => $title],
            $attrs + ['created_by' => $admin?->id, 'is_published' => true, 'registration_open' => true, 'popup_enabled' => true, 'area' => null],
        );

        $make('Parichay Sammelan', [
            'description' => "Samaj ke parivaron ka vaarshik parichay sammelan.\nSabhi sadasya aamantrit hain.",
            'starts_at' => now()->addDay()->setTime(11, 0),
            'ends_at' => now()->addDay()->setTime(16, 0),
            'place' => 'Samaj Bhavan, Nagpur',
            'reminder_interval_min' => 15,
        ]);

        $today = $make('Samaj Meeting (Aaj)', [
            'description' => 'Online meeting. Neeche Join now dabayein.',
            'starts_at' => now()->addHour()->startOfMinute(),
            'ends_at' => now()->addHours(3)->startOfMinute(),
            'place' => 'Online (Google Meet)',
            'join_url' => 'https://meet.google.com/demo-sample',
            'reminder_interval_min' => 10,
        ]);
        foreach (['9000000001', '9000000002', '9000000021'] as $phone) {
            if ($u = User::where('phone', $phone)->first()) {
                EventRegistration::updateOrCreate(['event_id' => $today->id, 'user_id' => $u->id], ['joined_at' => null]);
            }
        }

        $make('Holi Milan Samaroh', [
            'description' => 'Pichhle mahine ka sammelan.',
            'starts_at' => now()->subDays(20)->setTime(18, 0),
            'ends_at' => now()->subDays(20)->setTime(21, 0),
            'place' => 'Samaj Bhavan',
            'reminder_interval_min' => 15,
        ]);
    }
}
