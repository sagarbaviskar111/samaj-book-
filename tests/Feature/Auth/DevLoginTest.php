<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_dev_login_logs_in_in_local_env(): void
    {
        $this->app['env'] = 'local';
        $this->app['config']->set('app.debug', true);
        $u =User::forceCreate(['name' => 'Demo', 'phone' => '9000000001', 'password' => bcrypt('x'), 'role' => 'user', 'status' => 'active']);

        $this->withSession(['_token' => 't'])->post('/dev/login/9000000001', ['_token' => 't'], ['REMOTE_ADDR' => '127.0.0.1'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($u);
    }

    public function test_dev_login_is_404_outside_local(): void
    {
        $this->app['config']->set('app.debug', false);
        User::forceCreate(['name' => 'Demo', 'phone' => '9000000001', 'password' => bcrypt('x'), 'role' => 'user', 'status' => 'active']);

        $this->post('/dev/login/9000000001', [], ['REMOTE_ADDR' => '127.0.0.1'])->assertNotFound();
        $this->assertGuest();
    }
}
