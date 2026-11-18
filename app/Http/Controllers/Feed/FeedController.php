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
        $posts = self::visibleTo($user)
            ->with(['user:id,name,city,avatar', 'comments' => fn ($q) => $q->with('user:id,name,avatar')->limit(3)])
            ->latest('id')
            ->paginate(10)->withQueryString();
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

        return view('feed.index', compact('posts', 'stories', 'upcoming'));
    }
}
