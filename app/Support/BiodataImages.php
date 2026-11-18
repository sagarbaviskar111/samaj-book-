<?php

namespace App\Support;

use App\Models\BiodataPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * GD helpers: sanitize uploads (EXIF strip, downscale), and render
 * privacy-aware variants (blur, placeholder, viewer watermark).
 */
class BiodataImages
{
    public const MAX_PHOTOS = 6;
    public const MAX_EDGE = 1600;

    /** Re-encode an upload as a clean JPEG and store it. Returns the storage path or null if unreadable. */
    public static function store(UploadedFile $file, int $biodataId): ?string
    {
        $raw = @file_get_contents($file->getRealPath());
        $img = $raw ? @imagecreatefromstring($raw) : false;
        if (! $img) {
            return null;
        }

        // honour EXIF orientation before the metadata is dropped
        if (function_exists('exif_read_data') && in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            $exif = @exif_read_data($file->getRealPath());
            $rot = match ($exif['Orientation'] ?? 1) { 3 => 180, 6 => -90, 8 => 90, default => 0 };
            if ($rot && ($r = imagerotate($img, $rot, 0))) {
                $img = $r;
            }
        }

        $img = static::fit($img, static::MAX_EDGE);
        $path = "biodata/{$biodataId}/".Str::random(32).'.jpg';
        Storage::disk('public')->put($path, static::jpeg($img, 85));

        return $path;
    }

    /** Fit inside $max x $max, flatten on white (no alpha / metadata survives). */
    public static function fit(\GdImage $img, int $max): \GdImage
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, $max / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $out = imagecreatetruecolor($nw, $nh);
        imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
        imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

        return $out;
    }

    public static function jpeg(\GdImage $img, int $quality = 82): string
    {
        ob_start();
        imagejpeg($img, null, $quality);

        return (string) ob_get_clean();
    }

    /**
     * Cached, privacy-aware variant file path (absolute).
     *
     * @param 'clear'|'blur'|'placeholder' $mode
     */
    public static function variant(BiodataPhoto $photo, string $mode, string $size, int $viewerId, string $viewerName, bool $watermark): string
    {
        $dir = storage_path('app/private-cache');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $stamp = $photo->updated_at?->timestamp ?? 0;
        $key = sprintf('%d-%s-%s-%s-%d-%d.jpg', $photo->id, $mode, $size, $watermark ? 'w' : 'n', $watermark ? $viewerId : 0, $stamp);
        $file = $dir.DIRECTORY_SEPARATOR.$key;
        if (is_file($file)) {
            return $file;
        }

        $edge = $size === 'thumb' ? 400 : static::MAX_EDGE;

        if ($mode === 'placeholder') {
            $img = static::placeholder($edge);
        } else {
            $raw = Storage::disk('public')->exists($photo->path) ? Storage::disk('public')->get($photo->path) : null;
            $src = $raw ? @imagecreatefromstring($raw) : false;
            $img = $src ? static::fit($src, $edge) : static::placeholder($edge);
            if ($mode === 'blur') {
                $img = static::blur($img);
            }
        }

        if ($watermark) {
            static::watermark($img, 'SamajBook | '.Str::limit(Str::ascii($viewerName), 18, '').' | ID '.$viewerId);
        }

        file_put_contents($file, static::jpeg($img, 80));

        return $file;
    }

    public static function placeholder(int $edge): \GdImage
    {
        $w = (int) ($edge * 0.75);
        $img = imagecreatetruecolor($w, $edge);
        imagefill($img, 0, 0, imagecolorallocate($img, 237, 233, 254));
        $c = imagecolorallocate($img, 91, 60, 190);
        $msg = 'Photo is private';
        imagestring($img, 5, (int) (($w - imagefontwidth(5) * strlen($msg)) / 2), (int) ($edge / 2 - 7), $msg, $c);

        return $img;
    }

    public static function blur(\GdImage $img): \GdImage
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $small = imagecreatetruecolor(max(8, (int) ($w / 24)), max(8, (int) ($h / 24)));
        imagecopyresampled($small, $img, 0, 0, 0, 0, imagesx($small), imagesy($small), $w, $h);
        for ($i = 0; $i < 3; $i++) {
            imagefilter($small, IMG_FILTER_GAUSSIAN_BLUR);
        }
        $out = imagecreatetruecolor($w, $h);
        imagecopyresampled($out, $small, 0, 0, 0, 0, $w, $h, imagesx($small), imagesy($small));

        return $out;
    }

    public static function watermark(\GdImage $img, string $text): void
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $font = $w > 700 ? 5 : 3;
        $fw = imagefontwidth($font) * strlen($text);
        $light = imagecolorallocatealpha($img, 255, 255, 255, 80);
        $dark = imagecolorallocatealpha($img, 0, 0, 0, 90);
        for ($y = (int) ($h * 0.2); $y < $h; $y += (int) max(90, $h / 4)) {
            $x = (int) (($w - $fw) / 2);
            imagestring($img, $font, $x + 1, $y + 1, $text, $dark);
            imagestring($img, $font, $x, $y, $text, $light);
        }
        // bottom-right small tag
        imagestring($img, $font, max(4, $w - $fw - 6), $h - imagefontheight($font) - 6, $text, $light);
    }
}
