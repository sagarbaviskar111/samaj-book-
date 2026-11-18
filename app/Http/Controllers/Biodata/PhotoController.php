<?php

namespace App\Http\Controllers\Biodata;

use App\Http\Controllers\Controller;
use App\Http\Requests\Biodata\PhotoRequest;
use App\Models\Biodata;
use App\Models\BiodataPhoto;
use App\Support\BiodataAccess;
use App\Support\BiodataImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function store(PhotoRequest $request, Biodata $biodata): RedirectResponse
    {
        abort_unless(BiodataAccess::canManage($request->user(), $biodata), 403);

        $files = $request->file('photos');
        $room = BiodataImages::MAX_PHOTOS - $biodata->photos()->count();
        if (count($files) > $room) {
            return back()->withErrors(['photos' => "You can upload at most ".BiodataImages::MAX_PHOTOS." photos ({$room} slot(s) left)."]);
        }

        $added = 0;
        foreach ($files as $file) {
            $path = BiodataImages::store($file, $biodata->id);
            if (! $path) {
                continue;
            }
            $count = $biodata->photos()->count();
            $biodata->photos()->create(['path' => $path, 'is_primary' => $count === 0, 'sort' => $count]);
            $added++;
        }

        if ($added && $biodata->status === 'approved') {
            $biodata->status = 'pending'; // new photos need moderation
            $biodata->verified = false;
            $biodata->save();
        }

        return back()->with('status', $added ? "{$added} फ़ोटो जोड़ दी गई।" : 'यह फ़ोटो पढ़ी नहीं जा सकी। दूसरी फ़ोटो चुनिए।');
    }

    public function destroy(Request $request, BiodataPhoto $photo): RedirectResponse
    {
        $biodata = $photo->biodata;
        abort_unless(BiodataAccess::canManage($request->user(), $biodata), 403);

        $wasPrimary = $photo->is_primary;
        Storage::disk('public')->delete($photo->path);
        $photo->delete();
        if ($wasPrimary && ($next = $biodata->photos()->first())) {
            $next->update(['is_primary' => true]);
        }

        return back()->with('status', 'फ़ोटो हटा दी गई।');
    }

    public function primary(Request $request, BiodataPhoto $photo): RedirectResponse
    {
        $biodata = $photo->biodata;
        abort_unless(BiodataAccess::canManage($request->user(), $biodata), 403);

        $biodata->photos()->update(['is_primary' => false]);
        $photo->update(['is_primary' => true, 'sort' => 0]);
        $i = 1;
        foreach ($biodata->photos()->where('id', '!=', $photo->id)->get() as $other) {
            $other->update(['sort' => $i++]);
        }

        return back()->with('status', 'मुख्य फ़ोटो बदल दी गई।');
    }

    /** Privacy-enforcing photo endpoint. ?size=thumb|full */
    public function show(Request $request, BiodataPhoto $photo)
    {
        $user = $request->user();
        $biodata = $photo->biodata;
        $mode = BiodataAccess::photoMode($user, $biodata);
        abort_if($mode === 'none', 404);

        $size = $request->query('size') === 'thumb' ? 'thumb' : 'full';
        $privileged = BiodataAccess::isPrivileged($user, $biodata);

        // owner/admin: clear and un-watermarked; everyone else: watermarked with viewer identity
        $file = BiodataImages::variant($photo, $mode, $size, $user->id, $user->name, ! $privileged);

        return response()->file($file, [
            'Content-Type' => 'image/jpeg',
            'X-Content-Type-Options' => 'nosniff',
        ])->setPrivate()->setMaxAge(300);
    }
}
