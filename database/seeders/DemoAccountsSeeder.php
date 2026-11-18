<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Extra test accounts (same password as the other demo users: demo12345).
 *   9000000041-9000000043  => pending   (to test the admin approve/reject flow)
 *   9000000044-9000000045  => active, NO biodata yet (to test creating a biodata)
 * Run: php artisan db:seed --class=DemoAccountsSeeder
 */
class DemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $people = [
            ['9000000041', 'Amit Kharat',     'pending', 'Nagpur'],
            ['9000000042', 'Pooja Jadhav',    'pending', 'Pune'],
            ['9000000043', 'Santosh Wagh',    'pending', 'Nashik'],
            ['9000000044', 'Meena Chavan',    'active',  'Nagpur'],
            ['9000000045', 'Vikas Sonawane',  'active',  'Pune'],
        ];

        foreach ($people as [$phone, $name, $status, $city]) {
            User::updateOrCreate(['phone' => $phone], [
                'name' => $name,
                'password' => 'demo12345',
                'role' => 'user',
                'status' => $status,
                'city' => $city,
                'district' => $city,
                'state' => 'Maharashtra',
                'language' => 'hi',
            ]);
        }
    }
}
