<?php

namespace App\Support;

/**
 * Very light, centred logo used as the PDF page watermark.
 * public/images/pdf-watermark.png is (re)generated from the samaj logo whenever it is missing
 * or older than the logo (public/images/samaj-logo.png, else public/icons/icon-512.png).
 */
class PdfWatermark
{
    public const SIZE = 700;
    public const OPACITY = 0.09;

    public static function target(): string
    {
        return public_path('images/pdf-watermark.png');
    }

    public static function logo(): ?string
    {
        foreach (['images/samaj-logo.png', 'icons/icon-512.png'] as $rel) {
            if (is_file(public_path($rel))) {
                return public_path($rel);
            }
        }

        return null;
    }

    /** Path of an up-to-date watermark png (null if it cannot be produced). */
    public static function path(): ?string
    {
        $target = static::target();
        $logo = static::logo();

        $stale = ! is_file($target) || ($logo && filemtime($logo) > filemtime($target));
        if ($stale && $logo) {
            static::generate($logo, $target);
        }

        return is_file($target) ? $target : null;
    }

    public static function generate(string $logoPath, string $target): bool
    {
        $logo = @imagecreatefrompng($logoPath);
        if (! $logo) {
            return false;
        }
        $lw = imagesx($logo);
        $lh = imagesy($logo);
        $S = static::SIZE;

        $wm = imagecreatetruecolor($S, $S);
        imagealphablending($wm, false);
        imagesavealpha($wm, true);
        imagefill($wm, 0, 0, imagecolorallocatealpha($wm, 255, 255, 255, 127));

        $scale = min($S / $lw, $S / $lh);
        $nw = max(1, (int) ($lw * $scale));
        $nh = max(1, (int) ($lh * $scale));
        $tmp = imagecreatetruecolor($nw, $nh);
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        imagecopyresampled($tmp, $logo, 0, 0, 0, 0, $nw, $nh, $lw, $lh);

        // Opaque square logos (e.g. app icon): key out the flat background colour so no box shows.
        $bg = null;
        $probe = imagecolorat($tmp, (int) ($nw / 2), 2);
        if ((($probe >> 24) & 127) < 10) {
            $bg = [($probe >> 16) & 255, ($probe >> 8) & 255, $probe & 255];
        }

        $ox = (int) (($S - $nw) / 2);
        $oy = (int) (($S - $nh) / 2);
        for ($y = 0; $y < $nh; $y++) {
            for ($x = 0; $x < $nw; $x++) {
                $c = imagecolorat($tmp, $x, $y);
                $a = ($c >> 24) & 127;               // 0 opaque .. 127 transparent
                $opacity = (127 - $a) / 127;
                if ($bg) {
                    $d = sqrt((($c >> 16 & 255) - $bg[0]) ** 2 + (($c >> 8 & 255) - $bg[1]) ** 2 + (($c & 255) - $bg[2]) ** 2);
                    $opacity *= max(0, min(1, ($d - 45) / 70));
                }
                $newA = 127 - (int) round($opacity * 127 * static::OPACITY);
                imagesetpixel($wm, $ox + $x, $oy + $y, imagecolorallocatealpha($wm, ($c >> 16) & 255, ($c >> 8) & 255, $c & 255, $newA));
            }
        }

        $ok = @imagepng($wm, $target, 8);
        if ($ok) {
            clearstatcache(true, $target);
        }

        return $ok;
    }
}
