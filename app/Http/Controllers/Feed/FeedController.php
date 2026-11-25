<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\NewsItem;
use App\Models\Post;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /** Posts the viewer is allowed to see (visible + visibility rule). */
    public static function visibleTo($viewer)
    {
        if ($viewer->isPending()) {
            // not-yet-approved members only get the public (members) posts, read-only
            return Post::query()->where('status', 'visible')->where('visibility', 'members');
        }

        return Post::query()
            ->where('status', 'visible')
            ->where(function ($q) use ($viewer) {
                $q->where('visibility', 'members')
                    ->orWhere('user_id', $viewer->id)
                    ->orWhere(function ($q) use ($viewer) {
                        $q->where('visibility', 'city')
                            ->whereHas('user', function ($u) use ($viewer) {
                                $u->whereNotNull('city')->where('city', $viewer->city);
                            });
                    });
            });
    }

    public static function decorate($posts, $viewer): void
    {
        $ids = collect($posts)->pluck('id');
        $liked = Like::where('user_id', $viewer->id)->whereIn('post_id', $ids)->pluck('post_id')->all();
        foreach ($posts as $p) {
            $p->liked_by_me = in_array($p->id, $liked, true);
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $tab = $request->query('tab') === 'following' ? 'following' : 'for-you';
        $followingSub = "select following_id from follows where follower_id = ".(int) $user->id;

        $q = self::visibleTo($user)
            ->with(['user:id,name,city,avatar', 'comments' => fn ($q) => $q->with('user:id,name,avatar')->limit(3)]);
        if ($tab === 'following') {
            // only people I follow (plus my own posts)
            $q->where(fn ($w) => $w->whereRaw("posts.user_id in ($followingSub)")->orWhere('posts.user_id', $user->id));
        } else {
            // Instagram-style: posts of people I follow from the last 7 days come first, then everything else by time.
            $q->orderByRaw("case when posts.user_id in ($followingSub) and posts.created_at >= ? then 0 else 1 end", [now()->subDays(7)->toDateTimeString()]);
        }
        $posts = $q->latest('id')->paginate(10)->withQueryString();
        self::decorate($posts, $user);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('feed.partials.posts', ['posts' => $posts])->render(),
                'next' => $posts->nextPageUrl(),
            ]);
        }

        $stories = $posts->currentPage() === 1
            ? NewsItem::published()
                ->when($user->isPending(), fn ($q) => $q->whereIn('type', ['news', 'poster']))
                ->latest('id')->limit(12)->get()
            : collect();

        // Events are never queried for members awaiting approval.
        $upcoming = $user->isPending() ? [] : \Illuminate\Support\Facades\Cache::remember('feed.upcoming-events', 600, function () {
            return NewsItem::published()->where('type', 'event')->where('event_at', '>=', now())
                ->orderBy('event_at')->limit(4)->get(['id', 'title', 'event_at'])
                ->map(fn ($e) => ['id' => $e->id, 'title' => $e->title, 'at' => $e->event_at?->timestamp])->all();
        });

        // Members worth following: most-followed active members I don't follow yet (same city first).
        $suggestions = $user->isPending() ? collect() : \App\Models\User::query()
            ->where('role', 'user')->where('status', 'active')->where('id', '!=', $user->id)
            ->whereRaw("id not in ($followingSub)")
            ->withCount('followers')
            ->orderByRaw('case when city = ? then 0 else 1 end', [$user->city ?? ''])
            ->orderByDesc('followers_count')->latest('id')->limit(5)->get(['id', 'name', 'city', 'avatar']);

        return view('feed.index', compact('posts', 'stories', 'upcoming', 'tab', 'suggestions'));
    }
}
