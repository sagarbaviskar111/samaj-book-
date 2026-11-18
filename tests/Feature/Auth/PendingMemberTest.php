<?php

namespace Tests\Feature\Auth;

use App\Models\Biodata;
use App\Models\Event;
use App\Models\NewsItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingMemberTest extends TestCase
{
    use RefreshDatabase;

    private int $n = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function mk(string $status = 'pending', string $role = 'user'): User
    {
        $this->n++;

        return User::forceCreate([
            'name' => "Member {$this->n}", 'phone' => '93000000'.str_pad((string) $this->n, 2, '0', STR_PAD_LEFT),
            'password' => 'secret123', 'role' => $role, 'status' => $status, 'city' => 'Pune',
        ]);
    }

    public function test_pending_sees_read_only_feed_with_banner(): void
    {
        $author = $this->mk('active');
        $author->posts()->create(['body' => 'Namaste samaj', 'visibility' => 'members', 'status' => 'visible']);
        $author->posts()->create(['body' => 'Sirf shehar wale', 'visibility' => 'city', 'status' => 'visible']);
        NewsItem::create(['title' => 'Samaj Khabar', 'type' => 'news']);
        NewsItem::create(['title' => 'Naya Poster', 'type' => 'poster']);
        NewsItem::create(['title' => 'Mahasammelan Secret', 'type' => 'event', 'event_at' => now()->addDays(3)]);
        Event::create(['title' => 'Sammelan Hidden', 'starts_at' => now()->addDays(2), 'is_published' => true, 'registration_open' => true, 'popup_enabled' => true, 'reminder_interval_min' => 15]);

        $this->actingAs($this->mk())->get(route('home'))->assertOk()
            ->assertSee('एडमिन की मंज़ूरी बाकी है')
            ->assertSee('आपका अकाउंट मंज़ूरी का इंतज़ार कर रहा है')
            ->assertSee('Namaste samaj')
            ->assertSee('Samaj Khabar')
            ->assertSee('Naya Poster')
            ->assertDontSee('Sirf shehar wale')
            ->assertDontSee('Mahasammelan Secret')
            ->assertDontSee('Sammelan Hidden')
            ->assertDontSee('आने वाले कार्यक्रम')
            ->assertDontSee('आपके इलाके के नए बायोडाटा')
            ->assertDontSee('समाज के साथ कुछ साझा करें')
            ->assertDontSee('ev-pop-cfg', false)
            ->assertDontSee('data-like="', false)
            ->assertDontSee('name="body"', false)
            ->assertDontSee('पोस्ट की शिकायत करें')
            ->assertDontSee('+ पोस्ट करें')
            ->assertDontSee('href="'.route('biodata.index').'"', false)
            ->assertDontSee('href="'.route('events.index').'"', false)
            ->assertDontSee('href="'.route('interests.index').'"', false)
            ->assertDontSee('href="'.route('shortlist.index').'"', false)
            ->assertDontSee('href="'.route('posts.create').'"', false);
    }

    public function test_pending_is_blocked_from_member_only_routes(): void
    {
        $p = $this->mk();
        $b = Biodata::factory()->create();
        $post = $this->mk('active')->posts()->create(['body' => 'x', 'visibility' => 'members', 'status' => 'visible']);

        foreach ([
            route('biodata.index'), route('events.index'), route('interests.index'), route('biodata.show', $b),
            route('posts.create'), route('shortlist.index'), route('looking-for.create'), route('biodata.mine'),
        ] as $url) {
            $this->actingAs($p)->get($url)->assertRedirect(route('home'))->assertSessionHas('pending_notice');
        }

        $this->actingAs($p)->getJson(route('events.popup'))->assertStatus(403)->assertJson(['message' => 'एडमिन की मंज़ूरी बाकी है']);
        $this->actingAs($p)->postJson(route('posts.like', $post))->assertStatus(403)->assertJson(['message' => 'एडमिन की मंज़ूरी बाकी है']);
        $this->actingAs($p)->post(route('comments.store', $post), ['body' => 'hi'])->assertRedirect(route('home'));
        $this->assertSame(0, $post->fresh()->likes_count);
    }

    public function test_pending_can_use_whitelisted_pages(): void
    {
        $p = $this->mk();
        $post = $this->mk('active')->posts()->create(['body' => 'Dikhne wali post', 'visibility' => 'members', 'status' => 'visible']);
        $city = $this->mk('active')->posts()->create(['body' => 'x', 'visibility' => 'city', 'status' => 'visible']);

        $this->actingAs($p)->get(route('posts.show', $post))->assertOk()->assertSee('Dikhne wali post')->assertDontSee('data-like="', false);
        $this->actingAs($p)->get(route('posts.show', $city))->assertNotFound();
        $this->actingAs($p)->get(route('profile.show'))->assertOk()->assertSee('मंज़ूरी बाकी')
            ->assertDontSee('मेरे बायोडाटा')->assertDontSee('पसंद की सूची')->assertDontSee('बायोडाटा कितना पूरा है');
        $this->actingAs($p)->get(route('profile.edit'))->assertOk();
        $this->actingAs($p)->get(route('notifications.index'))->assertOk();
        $this->actingAs($p)->get(route('account.status'))->assertOk();
    }

    public function test_event_news_item_is_hidden_from_pending_but_not_active(): void
    {
        $ev = NewsItem::create(['title' => 'Mahasammelan Secret', 'type' => 'event', 'event_at' => now()->addDays(3)]);
        $news = NewsItem::create(['title' => 'Khabar', 'type' => 'news']);

        $this->actingAs($this->mk())->get(route('news.show', $ev))->assertNotFound();
        $this->actingAs($this->mk())->get(route('news.show', $news))->assertOk()->assertDontSee('Mahasammelan Secret');
        $this->actingAs($this->mk('active'))->get(route('news.show', $ev))->assertOk();
    }

    public function test_rejected_user_only_sees_status_page(): void
    {
        $r = $this->mk('rejected');
        $this->actingAs($r)->get(route('home'))->assertRedirect(route('account.status'));
        $this->actingAs($r)->get(route('profile.show'))->assertRedirect(route('account.status'));
        $this->actingAs($r)->get(route('account.status'))->assertOk();
    }

    public function test_approval_grants_full_access_immediately(): void
    {
        $p = $this->mk();
        $admin = $this->mk('active', 'admin');

        $this->actingAs($p)->get(route('biodata.index'))->assertRedirect(route('home'));

        $this->actingAs($admin)->post(route('admin.users.approve', $p->id))->assertRedirect();
        $this->assertSame('active', $p->fresh()->status);

        $this->actingAs($p->fresh())->get(route('biodata.index'))->assertOk();
        $this->actingAs($p->fresh())->get(route('events.index'))->assertOk();
        $this->actingAs($p->fresh())->get(route('home'))->assertOk()
            ->assertDontSee('एडमिन की मंज़ूरी बाकी है')->assertSee('समाज के साथ कुछ साझा करें');
    }

    public function test_register_and_login_send_pending_to_home(): void
    {
        $this->post('/register', [
            'name' => 'Ramesh', 'phone' => '9811122233', 'password' => 'password1', 'password_confirmation' => 'password1',
            'state' => 'Rajasthan', 'district' => 'Jaipur', 'city' => 'Jaipur', 'consent' => '1',
        ])->assertRedirect(route('home'));

        auth()->logout();
        $u = $this->mk();
        $this->post('/login', ['phone' => $u->phone, 'password' => 'secret123'])->assertRedirect(route('home'));
    }
}
