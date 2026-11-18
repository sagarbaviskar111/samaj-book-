<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Models\Interest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->isPending()) {
            // simplified view: no biodata / interests / shortlist queries at all
            return view('profile.show', ['user' => $user, 'pending' => true]);
        }

        $biodatas = $user->biodatas()->with('primaryPhoto')->latest()->get();

        $stats = [
            'posts' => $user->posts()->count(),
            'biodatas' => $biodatas->count(),
            'received' => Interest::where('to_user_id', $user->id)->where('status', 'sent')->count(),
            'sent' => Interest::where('from_user_id', $user->id)->count(),
            'accepted' => Interest::where('status', 'accepted')
                ->where(fn ($q) => $q->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id))->count(),
            'shortlist' => $user->shortlists()->count(),
            'unread' => $user->unreadNotifications()->count(),
        ];

        // the profile-completeness hint uses the best (most complete) biodata
        $completeness = $biodatas->map(fn ($b) => $b->completeness())->max() ?? 0;

        return view('profile.show', [
            'user' => $user,
            'stats' => $stats,
            'biodatas' => $biodatas,
            'completeness' => $completeness,
            'recentPosts' => $user->posts()->where('status', 'visible')->latest()->limit(6)->get(),
        ]);
    }

    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->safe()->only(['name', 'email', 'gotra', 'state', 'district', 'city', 'area']);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $path;
        }

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        $user->fill($data)->save();

        return redirect()->route('profile.show')->with('status', __('auth.profile_updated'));
    }

    /** DPDP Act: members can delete their own account and all their data. */
    public function destroy(Request $request)
    {
        $user = $request->user();

        $request->validate(['confirm_password' => ['required', 'string']]);
        if (! Hash::check($request->input('confirm_password'), $user->password)) {
            return back()->withErrors(['confirm_password' => __('पासवर्ड सही नहीं है।')], 'delete');
        }
        abort_if($user->isAdmin(), 403); // admins are removed from the admin panel

        foreach ($user->biodatas()->with('photos')->get() as $b) {
            foreach ($b->photos as $p) {
                Storage::disk('public')->delete($p->path);
            }
        }
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $user->delete(); // cascades to biodata, interests, posts, likes, comments...

        return redirect('/')->with('status', __('आपका अकाउंट और डेटा हटा दिया गया है।'));
    }

    public function locale(Request $request, string $locale)
    {
        abort_unless(in_array($locale, SetLocale::SUPPORTED, true), 404);

        session(['locale' => $locale]);
        if ($user = $request->user()) {
            $user->forceFill(['language' => $locale])->save();
        }

        return redirect()->back(fallback: '/');
    }
}
