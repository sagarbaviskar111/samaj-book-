<?php

namespace Database\Seeders;

use App\Models\Biodata;
use App\Models\LookingForProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo data for the access modes and on-behalf requests (idempotent; password demo12345).
 *   9000000044 Meena Chavan, 9000000045 Vikas Sonawane : active, no biodata, no looking-for => LITE mode
 *   9000000046 Kiran Pawar                              : active, looking-for (groom for relative) => VIEW-ONLY
 *   9000000001 Rahul Gaikwad gets "Neha Gaikwad" (cousin, approved female) to test on-behalf requests
 * Run: php artisan db:seed --class=DemoAccessSeeder
 */
class DemoAccessSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['9000000044', 'Meena Chavan', 'Nagpur'], ['9000000045', 'Vikas Sonawane', 'Pune']] as [$phone, $name, $city]) {
            $u = User::firstOrNew(['phone' => $phone]);
            if (! $u->exists) {
                $u->fill(['name' => $name, 'password' => 'demo12345', 'district' => $city, 'state' => 'Maharashtra', 'language' => 'hi']);
            }
            $u->forceFill(['role' => 'user', 'status' => 'active'])->save();
        }

        $kiran = User::firstOrNew(['phone' => '9000000046']);
        if (! $kiran->exists) {
            $kiran->fill(['name' => 'Kiran Pawar', 'password' => 'demo12345', 'district' => 'Nagpur', 'state' => 'Maharashtra', 'language' => 'hi']);
        }
        $kiran->forceFill(['role' => 'user', 'status' => 'active'])->save();
        LookingForProfile::updateOrCreate(['user_id' => $kiran->id], [
            'for_whom' => 'relative', 'looking_for' => 'groom', 'age_min' => 24, 'age_max' => 30,
            'education_level' => 'graduate', 'district' => 'Nagpur', 'note' => 'Bhatiji ke liye rishta dekh rahe hain.',
        ]);

        $rahul = User::where('phone', '9000000001')->first();
        if ($rahul) {
            Biodata::unguarded(fn () => Biodata::updateOrCreate(
                ['user_id' => $rahul->id, 'name' => 'Neha Gaikwad'],
                [
                    'added_by' => 'cousin', 'status' => 'approved', 'verified' => false, 'approved_at' => now(), 'views' => 0,
                    'gender' => 'female', 'dob' => now()->subYears(23)->subDays(20)->toDateString(), 'height_cm' => 158,
                    'marital_status' => 'never_married', 'education' => 'B.Com', 'education_level' => 'graduate',
                    'occupation' => 'Accountant', 'occupation_type' => 'private', 'income_range' => '2_5', 'income_private' => true,
                    'father_name' => 'Shri Gaikwad', 'mother_name' => 'Smt. Gaikwad', 'family_type' => 'joint',
                    'village' => 'Kamptee', 'district' => 'Nagpur', 'state' => 'Maharashtra', 'current_city' => 'Nagpur', 'area' => 'Kamptee',
                    'about' => 'Simple aur family-oriented.', 'expectation' => 'Padha-likha aur sanskari jeevansathi.',
                    'pref_age_min' => 24, 'pref_age_max' => 31, 'pref_education_level' => 'graduate', 'pref_district' => 'Nagpur',
                    'contact_phone' => '9000000001', 'photo_privacy' => 'members',
                ]
            ));
        }
    }
}
