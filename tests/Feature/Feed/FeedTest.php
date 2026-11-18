<?php

namespace Tests\Feature\Feed;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PushSubscription;
use App\Models\Report;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    private static int $n = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function member(string $city = 'Jaipur', string $name = 'Member'): User
    {
        self::$n++;

        return User::forceCreate([
            'name' => $name.self::$n,
            'phone' => '90000'.str_pad((string) self::$n, 5, '0', STR_PAD_LEFT),
            'password' => 'secret123',
            'role' => 'user',
            'status' => 'active',
            'city' => $city,
        ]);
    }

    private function mkPost(User $u, array $o = []): Post
    {
        return $u->posts()->create(array_merge(['body' => 'Hello '.uniqid(), 'visibility' => 'members', 'status' => 'visible'], $o));
    }

    public function test_feed_shows_visible_posts_only(): void
    {
        $a = $this->member();
        $b = $this->member();
        $this->mkPost($b, ['body' => 'SHOWN-POST']);
        $this->mkPost($b, ['body' => 'HIDDEN-POST', 'status' => 'hidden']);

        $this->actingAs($a)->get('/home')->assertOk()->assertSee('SHOWN-POST')->assertDontSee('HIDDEN-POST');
    }

    public function test_city_visibility(): void
    {
        $viewer = $this->member('Jaipur');
        $same = $this->member('Jaipur');
        $other = $this->member('Delhi');
        $this->mkPost($same, ['body' => 'SAME-CITY', 'visibility' => 'city']);
        $this->mkPost($other, ['body' => 'OTHER-CITY', 'visibility' => 'city']);

        $this->actingAs($viewer)->get('/home')->assertSee('SAME-CITY')->assertDontSee('OTHER-CITY');
    }

    public function test_feed_requires_login(): void
    {
        $this->get('/home')->assertRedirect();
    }

    public function test_like_toggle_counts_and_single_notification(): void
    {
        $owner = $this->member();
        $liker = $this->member();
        $post = $this->mkPost($owner);

        $this->actingAs($liker)->postJson(route('posts.like', $post))->assertJson(['liked' => true, 'count' => 1]);
        $this->actingAs($liker)->postJson(route('posts.like', $post))->assertJson(['liked' => false, 'count' => 0]);
        $this->actingAs($liker)->postJson(route('posts.like', $post))->assertJson(['liked' => true, 'count' => 1]);

        $this->assertSame(1, $owner->notifications()->count());
        $this->assertSame(1, $post->fresh()->likes_count);
    }

    public function test_self_like_does_not_notify(): void
    {
        $owner = $this->member();
        $post = $this->mkPost($owner);
        $this->actingAs($owner)->postJson(route('posts.like', $post))->assertOk();
        $this->assertSame(0, $owner->notifications()->count());
    }

    public function test_comment_counts_notify_and_delete(): void
    {
        $owner = $this->member();
        $c = $this->member();
        $post = $this->mkPost($owner);

        $res = $this->actingAs($c)->postJson(route('comments.store', $post), ['body' => 'Nice one'])->assertCreated();
        $this->assertSame(1, $post->fresh()->comments_count);
        $this->assertSame('comment', $owner->notifications()->first()->data['type']);

        $comment = Comment::first();
        $stranger = $this->member();
        $this->actingAs($stranger)->deleteJson(route('comments.destroy', $comment))->assertForbidden();
        $this->actingAs($c)->deleteJson(route('comments.destroy', $comment))->assertOk();
        $this->assertSame(0, $post->fresh()->comments_count);

        $this->actingAs($c)->postJson(route('comments.store', $post), ['body' => ''])->assertStatus(422);
    }

    public function test_post_image_validation_and_reencode(): void
    {
        Storage::fake('public');
        $u = $this->member();

        $this->actingAs($u)->post(route('posts.store'), ['visibility' => 'members', 'image' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('image');
        $this->actingAs($u)->post(route('posts.store'), ['visibility' => 'members', 'image' => UploadedFile::fake()->image('big.jpg')->size(6000)])
            ->assertSessionHasErrors('image');
        $this->actingAs($u)->post(route('posts.store'), ['visibility' => 'members', 'body' => str_repeat('a', 1501)])
            ->assertSessionHasErrors('body');

        $this->actingAs($u)->post(route('posts.store'), ['visibility' => 'members', 'body' => 'pic', 'image' => UploadedFile::fake()->image('big.jpg', 2400, 1800)])
            ->assertRedirect(route('home'));
        $post = Post::first();
        Storage::disk('public')->assertExists($post->image);
        [$w] = getimagesizefromstring(Storage::disk('public')->get($post->image));
        $this->assertSame(1600, $w);
    }

    public function test_only_owner_or_admin_can_delete_post(): void
    {
        $owner = $this->member();
        $other = $this->member();
        $post = $this->mkPost($owner);

        $this->actingAs($other)->delete(route('posts.destroy', $post))->assertForbidden();
        $this->actingAs($owner)->delete(route('posts.destroy', $post))->assertRedirect();
        $this->assertNull(Post::find($post->id));
    }

    public function test_report_duplicate_guard(): void
    {
        $a = $this->member();
        $b = $this->member();
        $post = $this->mkPost($b);
        $payload = ['target_type' => 'post', 'target_id' => $post->id, 'reason' => 'spam'];

        $this->actingAs($a)->postJson(route('reports.store'), $payload)->assertJson(['duplicate' => false]);
        $this->actingAs($a)->postJson(route('reports.store'), $payload)->assertJson(['duplicate' => true]);
        $this->assertSame(1, Report::count());
        $this->actingAs($a)->postJson(route('reports.store'), ['target_type' => 'bad', 'target_id' => 1])->assertStatus(422);
    }

    public function test_notifications_read_flow(): void
    {
        $u = $this->member();
        $u->notify(new GeneralNotification('like', 'Someone liked', null, '/home'));
        $u->notify(new GeneralNotification('system', 'Welcome', null, null));

        $this->actingAs($u)->get(route('notifications.index'))->assertOk()->assertSee('Someone liked');

        $n = $u->notifications()->where('data', 'like', '%Someone liked%')->first();
        $this->actingAs($u)->post(route('notifications.read', $n->id))->assertRedirect('/home');
        $this->assertNotNull($n->fresh()->read_at);
        $this->assertSame(1, $u->unreadNotifications()->count());

        $this->actingAs($u)->post(route('notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $u->unreadNotifications()->count());
    }

    public function test_notification_settings_hide_opted_out_types(): void
    {
        $u = $this->member();
        $u->notify(new GeneralNotification('like', 'LIKE-ITEM', null, null));
        $u->notify(new GeneralNotification('comment', 'COMMENT-ITEM', null, null));

        $this->actingAs($u)->post(route('notifications.settings.save'), ['prefs' => ['comment' => '1', 'system' => '1']])->assertRedirect();
        $this->actingAs($u)->get(route('notifications.settings'))->assertOk();
        $this->actingAs($u)->get(route('notifications.index'))->assertSee('COMMENT-ITEM')->assertDontSee('LIKE-ITEM');
    }

    public function test_push_subscribe_and_unsubscribe(): void
    {
        $u = $this->member();
        $payload = ['endpoint' => 'https://push.example.com/abc', 'keys' => ['p256dh' => 'k1', 'auth' => 'a1']];

        $this->actingAs($u)->postJson(route('push.subscribe'), $payload)->assertOk();
        $this->actingAs($u)->postJson(route('push.subscribe'), $payload)->assertOk();
        $this->assertSame(1, PushSubscription::where('user_id', $u->id)->count());

        $this->actingAs($u)->postJson(route('push.subscribe'), ['endpoint' => 'nope'])->assertStatus(422);
        $this->actingAs($u)->postJson(route('push.unsubscribe'), ['endpoint' => $payload['endpoint']])->assertOk();
        $this->assertSame(0, PushSubscription::count());
    }

    public function test_notify_without_vapid_keys_is_silent(): void
    {
        $u = $this->member();
        PushSubscription::create(['user_id' => $u->id, 'endpoint' => 'https://push.example.com/z', 'p256dh' => 'a', 'auth' => 'b']);
        $u->notify(new GeneralNotification('news', 'Hi', 'Body', '/home'));
        $this->assertSame(1, $u->notifications()->count());
    }
}
