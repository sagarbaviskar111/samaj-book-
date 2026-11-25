<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    public function toggle(Request $request, Post $post)
    {
        $user = $request->user();
        abort_unless(FeedController::visibleTo($user)->whereKey($post->id)->exists(), 404);

        $liked = DB::transaction(function () use ($user, $post) {
            $existing = Like::where('user_id', $user->id)->where('post_id', $post->id)->first();
            if ($existing) {
                $existing->delete();
                Post::whereKey($post->id)->where('likes_count', '>', 0)->decrement('likes_count');

                return false;
            }
            Like::create(['user_id' => $user->id, 'post_id' => $post->id]);
            Post::whereKey($post->id)->increment('likes_count');

            return true;
        });

        if ($liked && $post->user_id !== $user->id) {
            $this->notifyOnce($post, $user);
        }

        $count = (int) Post::whereKey($post->id)->value('likes_count');

        return $request->expectsJson()
            ? response()->json(['liked' => $liked, 'count' => $count])
            : back();
    }

    /** One like notification per (liker, post), even after unlike/like again. */
    private function notifyOnce(Post $post, $liker): void
    {
        $owner = $post->user;
        $title = __(':name liked your post', ['name' => $liker->name]);
        $url = route('posts.show', $post);

        $dup = $owner->notifications()
            ->where('data', 'like', '%'.substr(json_encode($title), 1, -1).'%')
            ->where('data', 'like', '%'.substr(json_encode($url), 1, -1).'%')
            ->exists();
        if (! $dup) {
            $owner->notify(new GeneralNotification('like', $title, null, $url));
        }
    }
}
