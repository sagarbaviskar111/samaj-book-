<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // Stub routes owned by other modules so shared layouts render.
        foreach (['home', 'biodata.index', 'posts.create', 'notifications.index'] as $name) {
            if (! Route::has($name)) {
                Route::middleware('web')->get('/_stub/'.$name, fn () => 'ok')->name($name);
            }
        }
        app('router')->getRoutes()->refreshNameLookups();
    }

    private function member(array $attrs = []): User
    {
        $u = new User(array_merge([
            'name' => 'Test User', 'phone' => '9876543210', 'password' => 'secret123',
        ], $attrs));
        $u->role = $attrs['role'] ?? 'user';
        $u->status = $attrs['status'] ?? 'pending';
        $u->save();

        return $u;
    }

    private function registerData(array $over = []): array
    {
        return array_merge([
            'name' => 'Ramesh', 'phone' => '9811122233', 'password' => 'password1', 'password_confirmation' => 'password1',
            'state' => 'Rajasthan', 'district' => 'Jaipur', 'city' => 'Jaipur', 'consent' => '1',
        ], $over);
    }

    public function test_register_creates_pending_user_and_redirects_to_home(): void
    {
        $this->post('/register', $this->registerData())->assertRedirect(route('home'));
        $u = User::where('phone', '9811122233')->first();
        $this->assertSame('pending', $u->status);
        $this->assertSame('user', $u->role);
        $this->assertAuthenticatedAs($u);
    }

    public function test_register_requires_consent_and_valid_phone(): void
    {
        $this->post('/register', $this->registerData(['consent' => null]))->assertSessionHasErrors('consent');
        $this->post('/register', $this->registerData(['phone' => '12345']))->assertSessionHasErrors('phone');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_login_success_and_failure(): void
    {
        $this->member(['status' => 'active']);
        $this->post('/login', ['phone' => '9876543210', 'password' => 'wrong'])->assertSessionHasErrors('phone');
        $this->assertGuest();
        $this->post('/login', ['phone' => '9876543210', 'password' => 'secret123'])->assertRedirect();
        $this->assertAuthenticated();
        $this->assertNotNull(User::first()->last_seen_at);
    }

    public function test_blocked_user_cannot_login(): void
    {
        $this->member(['status' => 'blocked']);
        $this->post('/login', ['phone' => '9876543210', 'password' => 'secret123'])->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_login_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['phone' => '9876543210', 'password' => 'bad']);
        }
        $this->member(['status' => 'active']);
        $this->post('/login', ['phone' => '9876543210', 'password' => 'secret123'])->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_status_page_by_state(): void
    {
        $pending = $this->member();
        $this->actingAs($pending)->get('/account/status')->assertOk()->assertSee('एडमिन की मंज़ूरी');

        $pending->forceFill(['status' => 'rejected', 'reject_reason' => 'Photo unclear'])->save();
        $this->actingAs($pending)->get('/account/status')->assertOk()->assertSee('Photo unclear');

        $pending->forceFill(['status' => 'active'])->save();
        $this->actingAs($pending)->get('/account/status')->assertRedirect(route('home'));
    }

    public function test_logout(): void
    {
        $this->actingAs($this->member(['status' => 'active']))->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_profile_update_and_password_change(): void
    {
        Storage::fake('public');
        $u = $this->member(['status' => 'active', 'state' => 'X', 'district' => 'Y', 'city' => 'Z']);

        $this->actingAs($u)->get('/profile')->assertOk();
        $this->actingAs($u)->post('/profile', [
            'name' => 'New Name', 'state' => 'MP', 'district' => 'Indore', 'city' => 'Indore',
            'current_password' => 'secret123', 'password' => 'newpass123', 'password_confirmation' => 'newpass123',
            'avatar' => UploadedFile::fake()->image('a.jpg', 200, 200),
        ])->assertRedirect(route('profile.show'));

        $u->refresh();
        $this->assertSame('New Name', $u->name);
        $this->assertTrue(Hash::check('newpass123', $u->password));
        Storage::disk('public')->assertExists($u->avatar);
    }

    public function test_avatar_validation(): void
    {
        Storage::fake('public');
        $u = $this->member(['status' => 'active']);
        $base = ['name' => 'N', 'state' => 'a', 'district' => 'b', 'city' => 'c'];

        $this->actingAs($u)->post('/profile', $base + ['avatar' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('avatar');
        $this->actingAs($u)->post('/profile', $base + ['avatar' => UploadedFile::fake()->image('big.jpg')->size(5000)])
            ->assertSessionHasErrors('avatar');
    }

    public function test_rejected_user_cannot_open_profile(): void
    {
        $this->actingAs($this->member(['status' => 'rejected']))->get('/profile')->assertRedirect(route('account.status'));
    }

    public function test_locale_switch_stores_language(): void
    {
        $u = $this->member(['status' => 'active']);
        $this->actingAs($u)->get('/lang/en')->assertRedirect();
        $this->assertSame('en', $u->fresh()->language);
        $this->get('/lang/zz')->assertNotFound();
    }
}
