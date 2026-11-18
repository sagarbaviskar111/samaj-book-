<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates the one Superadmin (above the Admin). In production create it with
 *   php artisan superadmin:create <phone> "<name>"
 * and never keep the default password below.
 * Run: php artisan db:seed --class=SuperAdminSeeder
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('phone', env('SUPERADMIN_PHONE', '9999999990'))->first() ?? new User;

        $user->forceFill([
            'phone' => env('SUPERADMIN_PHONE', '9999999990'),
            'name' => 'Super Admin',
            'email' => env('SUPERADMIN_EMAIL', 'superadmin@samajbook.test'),
            'password' => env('SUPERADMIN_PASSWORD', 'super12345'),
            'role' => 'superadmin',
            'status' => 'active',
            'language' => 'hi',
        ])->save();
    }
}
