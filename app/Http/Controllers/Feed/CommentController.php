<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $user = $request->user();
        abort_unless(FeedController::visibleTo($user)->whereKey($post->id)->exists(), 404);
        $data = $request->validate(['body' => ['required', 'string', 'max:1000']]);

        $comment = Comment::create(['user_id' => $user->id, 'post_id' => $post->id, 'body' => trim($data['body'])]);
        Post::whereKey($post->id)->increment('comments_count');

        if ($post->user_id !== $user->id) {
            $post->user->notify(new GeneralNotification(
                'comment',
                __(':name commented on your post', ['name' => $user->name]),
                mb_strimwidth($comment->body, 0, 100, '...'),
                route('posts.show', $post)
            ));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'count' => (int) Post::whereKey($post->id)->value('comments_count'),
                'html' => view('feed.partials.comment', ['comment' => $comment->load('user:id,name,avatar'), 'post' => $post])->render(),
            ], 201);
        }

        return back()->with('status', __('Comment added.'));
    }

    public function destroy(Request $request, Comment $comment)
    {
        $user = $request->user();
        $post = $comment->post;
        abort_unless(
            $comment->user_id === $user->id || $post->user_id === $user->id || $user->isAdmin(),
            403
        );
        $comment->delete();
        Post::whereKey($post->id)->where('comments_count', '>', 0)->decrement('comments_count');

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'count' => (int) Post::whereKey($post->id)->value('comments_count')]);
        }

        return back()->with('status', __('Comment removed.'));
    }
}
