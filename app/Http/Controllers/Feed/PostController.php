<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function create()
    {
        return view('feed.create');
    }

    public function store(StorePostRequest $request)
    {
        $path = null;
        if ($request->hasFile('image')) {
            $path = $this->storeImage($request->file('image'));
            if (! $path) {
                return back()->withErrors(['image' => 'Could not process this image.'])->withInput();
            }
        }

        $request->user()->posts()->create([
            'body' => $request->input('body'),
            'image' => $path,
            'visibility' => $request->input('visibility'),
            'status' => 'visible',
        ]);

        return redirect()->route('home')->with('status', 'Your post is live.');
    }

    public function show(Request $request, Post $post)
    {
        $user = $request->user();
        abort_unless(
            FeedController::visibleTo($user)->whereKey($post->id)->exists() || $user->isAdmin(),
            404
        );
        $post->load(['user:id,name,city,avatar', 'comments' => fn ($q) => $q->with('user:id,name,avatar')]);
        FeedController::decorate(collect([$post]), $user);

        return view('feed.show', ['post' => $post]);
    }

    public function destroy(Request $request, Post $post)
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('home')->with('status', 'Post deleted.');
    }

    /** Re-encode through GD: strips EXIF, applies orientation, caps at 1600px. */
    private function storeImage($file): ?string
    {
        $data = @file_get_contents($file->getRealPath());
        $img = $data ? @imagecreatefromstring($data) : false;
        if (! $img) {
            return null;
        }
        if (function_exists('exif_read_data') && $file->getMimeType() === 'image/jpeg') {
            $exif = @exif_read_data($file->getRealPath());
            $rot = match ($exif['Orientation'] ?? 1) {
                3 => 180, 6 => -90, 8 => 90, default => 0,
            };
            if ($rot) {
                $img = imagerotate($img, $rot, 0) ?: $img;
            }
        }
        $w = imagesx($img);
        $h = imagesy($img);
        if (max($w, $h) > 1600) {
            $r = 1600 / max($w, $h);
            $img = imagescale($img, (int) round($w * $r), (int) round($h * $r));
        }
        $tmp = tempnam(sys_get_temp_dir(), 'sb');
        imagejpeg($img, $tmp, 85);
        $path = 'posts/'.Str::random(32).'.jpg';
        Storage::disk('public')->put($path, file_get_contents($tmp));
        @unlink($tmp);

        return $path;
    }
}
