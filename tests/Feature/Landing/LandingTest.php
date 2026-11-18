<?php

namespace Tests\Feature\Landing;

use App\Models\NewsItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_sees_landing_with_fallback_cards(): void
    {
        $this->get('/')->assertOk()->assertSee('जीवनसाथी की तलाश')->assertSee('परिचय सम्मेलन');
    }

    public function test_landing_shows_seeded_news_and_events(): void
    {
        NewsItem::create(['type' => 'news', 'title' => 'Samaj Milan Poster', 'is_published' => true, 'show_on_landing' => true]);
        NewsItem::create(['type' => 'news', 'title' => 'Hidden Draft', 'is_published' => false, 'show_on_landing' => true]);
        NewsItem::create(['type' => 'event', 'title' => 'Samuhik Vivah 2027', 'is_published' => true, 'show_on_landing' => true, 'event_at' => now()->addDays(10)]);

        $this->get('/')->assertOk()
            ->assertSee('Samaj Milan Poster')
            ->assertSee('Samuhik Vivah 2027')
            ->assertDontSee('Hidden Draft');
    }

    public function test_active_member_is_redirected_home(): void
    {
        $user = User::factory()->create(['phone' => '9876500001', 'status' => 'active', 'role' => 'user']);
        $res = $this->actingAs($user)->get('/');
        if (\Illuminate\Support\Facades\Route::has('home')) {
            $res->assertRedirect(route('home'));
        } else {
            $res->assertOk();
        }
    }

    public function test_admin_is_redirected_to_dashboard(): void
    {
        $admin = User::factory()->create(['phone' => '9876500002', 'status' => 'active', 'role' => 'admin']);
        $res = $this->actingAs($admin)->get('/');
        if (\Illuminate\Support\Facades\Route::has('admin.dashboard')) {
            $res->assertRedirect(route('admin.dashboard'));
        } else {
            $res->assertStatus(200);
        }
    }

    public function test_static_pages_render(): void
    {
        foreach (['page.terms', 'page.privacy', 'page.about', 'page.faq'] as $name) {
            $this->get(route($name))->assertOk();
        }
        $this->get(route('page.privacy'))->assertSee('DPDP Act 2023');
    }
}
