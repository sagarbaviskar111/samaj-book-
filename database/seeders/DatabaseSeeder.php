<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Single admin for now. Sub-admins (area wise) come later.
        User::updateOrCreate(['phone' => env('ADMIN_PHONE', '9999999999')], [
            'name' => 'Samaj Admin',
            'email' => env('ADMIN_EMAIL', 'admin@samajbook.test'),
            'password' => env('ADMIN_PASSWORD', 'admin12345'),
            'role' => 'admin',
            'status' => 'active',
            'language' => 'hi',
        ]);

        // One Superadmin above the Admin (see SuperAdminSeeder; use `php artisan superadmin:create` in production).
        $this->call(SuperAdminSeeder::class);
    }
}
