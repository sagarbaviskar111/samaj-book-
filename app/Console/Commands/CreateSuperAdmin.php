<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('superadmin:create {phone : 10-digit login phone} {name : Display name} {--password= : Password (prompted when omitted)}')]
#[Description('Create a superadmin account (the only way to create one)')]
class CreateSuperAdmin extends Command
{
    public function handle(): int
    {
        $phone = (string) $this->argument('phone');
        if (! preg_match('/^\d{10}$/', $phone)) {
            $this->error('Phone must be exactly 10 digits.');

            return self::FAILURE;
        }
        if (User::where('phone', $phone)->exists()) {
            $this->error('A user with this phone already exists.');

            return self::FAILURE;
        }

        $password = $this->option('password') ?: $this->secret('Password (min 8 characters)');
        if (strlen((string) $password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        $u = new User;
        $u->forceFill(['name' => $this->argument('name'), 'phone' => $phone, 'password' => $password, 'role' => 'superadmin', 'status' => 'active', 'language' => 'hi'])->save();
        AuditLog::create(['admin_id' => null, 'action' => AuditLog::SA_PREFIX.'superadmin.create', 'target_type' => 'user', 'target_id' => $u->id, 'note' => 'created via CLI: '.$u->name]);
        $this->info("Superadmin created: {$u->name} ({$u->phone}).");

        return self::SUCCESS;
    }
}
