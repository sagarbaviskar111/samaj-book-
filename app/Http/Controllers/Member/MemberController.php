<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Feed\FeedController;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    private function member(int|string $id): User
    {
        return User::where('role', 'user')->where('status', 'active')->findOrFail($id);
    }

    /** Instagram-style public profile: name, city, counts, follow button and their posts. No contact details. */
    public function show(Request $request, int $member)
    {
        $m = $this->member($member);
        $me = $request->user();

        $posts = FeedController::visibleTo($me)->where('user_id', $m->id)
            ->with(['user:id,name,city,avatar', 'comments' => fn ($q) => $q->with('user:id,name,avatar')->limit(3)])
            ->latest('id')->paginate(10)->withQueryString();
        FeedController::decorate($posts, $me);

        $stats = [
            'followers' => Follow::where('following_id', $m->id)->count(),
            'following' => Follow::where('follower_id', $m->id)->count(),
            'posts' => $m->id === $me->id ? $posts->total() : \App\Models\Post::where('user_id', $m->id)->where('status', 'visible')->where('visibility', 'members')->count(),
        ];
        $followsMe = $m->id !== $me->id && Follow::where('follower_id', $m->id)->where('following_id', $me->id)->exists();

        return view('members.show', compact('m', 'posts', 'stats', 'followsMe'));
    }

    public function followers(Request $request, int $member)
    {
        $m = $this->member($member);
        $ids = Follow::where('following_id', $m->id)->latest('id')->pluck('follower_id');

        return $this->listing($request, $m, $ids, 'followers');
    }

    public function following(Request $request, int $member)
    {
        $m = $this->member($member);
        $ids = Follow::where('follower_id', $m->id)->latest('id')->pluck('following_id');

        return $this->listing($request, $m, $ids, 'following');
    }

    private function listing(Request $request, User $m, $ids, string $kind)
    {
        $users = User::where('role', 'user')->where('status', 'active')->whereIn('id', $ids)
            ->get(['id', 'name', 'city', 'avatar'])
            ->sortBy(fn ($u) => $ids->search($u->id))->values();

        return view('members.list', compact('m', 'users', 'kind'));
    }
}
