<?php

namespace Tests\Feature\Superadmin;

use App\Models\Biodata;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class SuperadminTestCase extends TestCase
{
    use RefreshDatabase;

    private int $n = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function mk(string $role = 'user', string $status = 'active', array $extra = []): User
    {
        $this->n++;

        return User::unguarded(fn () => User::create(array_merge([
            'name' => "User {$this->n}", 'phone' => '92000000'.str_pad((string) $this->n, 2, '0', STR_PAD_LEFT),
            'password' => 'secret123', 'role' => $role, 'status' => $status,
        ], $extra)));
    }

    protected function bio(User $owner, array $extra = []): Biodata
    {
        return Biodata::unguarded(fn () => Biodata::create(array_merge([
            'user_id' => $owner->id, 'name' => 'Sita', 'gender' => 'female', 'dob' => '2000-01-01', 'status' => 'approved', 'district' => 'Nagpur',
        ], $extra)));
    }
}
