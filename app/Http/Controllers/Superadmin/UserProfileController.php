<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EventRegistration;
use App\Models\Interest;
use App\Models\LoginLog;
use App\Models\Payment;
use App\Models\Post;
use App\Models\User;

/** 360 degree profile of ANY account (including hidden biodata fields). Superadmin only. */
class UserProfileController extends Controller
{
    public function show(int $id)
    {
        $user = User::findOrFail($id);
        $biodatas = $user->biodatas()->with('photos')->latest()->get();
        $interestsSent = Interest::where('from_user_id', $user->id)->with(['toUser:id,name,phone', 'toBiodata:id,name'])->latest()->limit(50)->get();
        $interestsReceived = Interest::where('to_user_id', $user->id)->with(['fromUser:id,name,phone', 'fromBiodata:id,name'])->latest()->limit(50)->get();
        $posts = Post::where('user_id', $user->id)->latest()->limit(30)->get();
        $registrations = EventRegistration::where('user_id', $user->id)->with('event:id,title,starts_at')->latest()->limit(30)->get();
        $payments = Payment::where('user_id', $user->id)->latest('id')->limit(50)->get();
        $logins = LoginLog::where('user_id', $user->id)->latest('id')->limit(30)->get();
        $audit = AuditLog::where(fn ($q) => $q->where('target_type', 'user')->where('target_id', $user->id))
            ->orWhere('admin_id', $user->id)->with('admin:id,name')->latest('id')->limit(40)->get();

        return view('superadmin.users.show', compact('user', 'biodatas', 'interestsSent', 'interestsReceived', 'posts', 'registrations', 'payments', 'logins', 'audit'));
    }
}
