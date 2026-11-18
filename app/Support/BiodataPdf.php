<?php

namespace App\Support;

use App\Models\Biodata;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

/**
 * Builds the downloadable biodata PDF with mPDF (dompdf cannot shape Devanagari).
 * Devanagari text is routed to FreeSerif (OpenType layout) via lang/auto-script detection,
 * Latin text uses DejaVu Sans.
 */
class BiodataPdf
{
    /** View data for resources/views/biodata/pdf.blade.php (kept separate so tests can inspect it). */
    public static function data(Biodata $b, ?User $viewer, bool $wantContact = true): array
    {
        $isOwner = BiodataAccess::isOwner($viewer, $b);
        $photo = $b->photos()->orderByDesc('is_primary')->orderBy('sort')->first();

        return [
            'b' => $b,
            'viewer' => $viewer,
            'isOwner' => $isOwner,
            'photoData' => $photo ? static::photoData($photo->path) : null,
            'logoData' => static::fileData(PdfWatermark::logo(), 180),
            'ganeshData' => static::fileData(public_path('images/ganesh-240.png'), 160),
            'showIncome' => BiodataAccess::canSeeIncome($viewer, $b),
            'showContact' => $wantContact && BiodataAccess::canSeeContact($viewer, $b),
            // rashi/nakshatra are only ever printed on the owner's own download
            'showKundli' => $isOwner,
        ];
    }

    public static function render(Biodata $b, ?User $viewer, bool $wantContact = true): string
    {
        $html = view('biodata.pdf', static::data($b, $viewer, $wantContact))->render();

        $dir = storage_path('app/mpdf');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $defaults = (new ConfigVariables())->getDefaults();
        $fonts = (new FontVariables())->getDefaults();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 13, 'margin_right' => 13, 'margin_top' => 12, 'margin_bottom' => 17,
            'margin_footer' => 7,
            'tempDir' => $dir,
            'fontDir' => array_merge($defaults['fontDir'], []),
            'fontdata' => $fonts['fontdata'],
            'default_font' => 'dejavusans',
            'default_font_size' => 10,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'useSubstitutions' => true,
            'backupSubsFont' => ['freeserif'],
            'img_dpi' => 150,
        ]);
        $mpdf->SetTitle('Biodata - '.$b->name);
        $mpdf->SetAuthor('SamajBook');

        if ($wm = PdfWatermark::path()) {
            // 120mm centred on A4 (210x297), behind the text, on every page
            $mpdf->SetWatermarkImage($wm, 1, [120, 120], [45, 88.5]);
            $mpdf->showWatermarkImage = true;
            $mpdf->watermarkImgBehind = true;
        }

        $mpdf->SetHTMLFooter(view('biodata.pdf-footer')->render());
        $mpdf->WriteHTML($html);
        static::drawBorders($mpdf);

        return $mpdf->Output('', 'S');
    }

    /** Double marigold / maroon frame on every page, drawn straight into the page streams. */
    protected static function drawBorders(Mpdf $mpdf): void
    {
        $k = 72 / 25.4;
        $H = 297;
        $rect = function (float $inset, float $w, string $rgb) use ($k, $H) {
            $x = $inset * $k;
            $y = $inset * $k;
            $rw = (210 - 2 * $inset) * $k;
            $rh = ($H - 2 * $inset) * $k;

            return sprintf('q %.2F w %s RG %.2F %.2F %.2F %.2F re S Q ', $w * $k, $rgb, $x, $y, $rw, $rh);
        };
        $ops = $rect(5.5, 1.2, '0.933 0.620 0.039').$rect(7.6, 0.35, '0.439 0.102 0.141');
        foreach (array_keys($mpdf->pages) as $n) {
            $mpdf->pages[$n] .= "\n".$ops;
        }
    }

    /** Resample a stored photo to max 900px JPEG data URI. */
    protected static function photoData(string $path): ?string
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            return null;
        }
        $src = @imagecreatefromstring($disk->get($path));
        if (! $src) {
            return null;
        }

        return 'data:image/jpeg;base64,'.base64_encode(BiodataImages::jpeg(BiodataImages::fit($src, 900), 80));
    }

    /** Local png as a (downscaled) data URI, keeping alpha. */
    protected static function fileData(?string $file, int $max): ?string
    {
        if (! $file || ! is_file($file)) {
            return null;
        }
        $im = @imagecreatefrompng($file);
        if (! $im) {
            return null;
        }
        $w = imagesx($im);
        $h = imagesy($im);
        $s = min(1, $max / max($w, $h));
        $nw = max(1, (int) round($w * $s));
        $nh = max(1, (int) round($h * $s));
        $out = imagecreatetruecolor($nw, $nh);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
        imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
        ob_start();
        imagepng($out, null, 8);

        return 'data:image/png;base64,'.base64_encode(ob_get_clean());
    }
}
