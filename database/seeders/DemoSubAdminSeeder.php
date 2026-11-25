<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSubAdminSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['9888888801', 'Nashik'], ['9888888802', 'Pune']] as [$phone, $area]) {
            $u = User::firstOrNew(['phone' => $phone]);
            $u->forceFill([
                'name' => "Sub-admin {$area}",
                'password' => 'demo12345',
                'role' => 'subadmin',
                'status' => 'active',
                'admin_area' => $area,
                'language' => 'hi',
            ])->save();
        }
    }
}
