<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /** Follow / unfollow a member. Only approved members can follow, and only members can be followed. */
    public function toggle(Request $request, User $member)
    {
        $me = $request->user();
        abort_if($me->isPending() || $me->id === $member->id, 403);
        abort_unless($member->role === 'user' && $member->status === 'active', 404);

        $existing = Follow::where('follower_id', $me->id)->where('following_id', $member->id)->first();
        if ($existing) {
            $existing->delete();
            $following = false;
        } else {
            Follow::create(['follower_id' => $me->id, 'following_id' => $member->id]);
            $following = true;
            $this->notifyOnce($member, $me);
        }

        $followers = Follow::where('following_id', $member->id)->count();

        return $request->expectsJson()
            ? response()->json(['following' => $following, 'followers' => $followers])
            : back();
    }

    /** One "started following you" notification per (follower, member), even after unfollow/follow again. */
    private function notifyOnce(User $member, User $me): void
    {
        $title = __(':name started following you', ['name' => $me->name]);
        $url = route('members.show', $me);
        $dup = $member->notifications()
            ->where('data', 'like', '%'.substr(json_encode($url), 1, -1).'%')
            ->where('data', 'like', '%"follow"%')
            ->exists();
        if (! $dup) {
            $member->notify(new GeneralNotification('follow', $title, null, $url));
        }
    }
}
