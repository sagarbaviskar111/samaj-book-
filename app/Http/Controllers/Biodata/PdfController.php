<?php

namespace App\Http\Controllers\Biodata;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use App\Support\BiodataAccess;
use App\Support\BiodataPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    public function download(Request $request, Biodata $biodata)
    {
        $user = $request->user();
        abort_unless(BiodataAccess::canView($user, $biodata), 404);
        abort_unless(BiodataAccess::canDownloadPdf($user, $biodata), 403, __('PDF is available after an interest is accepted.'));

        // ?contact=0 hides the phone/WhatsApp lines (default: shown when access allows)
        $wantContact = $request->query('contact', '1') !== '0';
        $bytes = BiodataPdf::render($biodata, $user, $wantContact);

        $file = 'biodata-'.Str::slug($biodata->name ?: 'profile').'-'.$biodata->id.'.pdf';

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$file.'"',
            'Content-Length' => strlen($bytes),
        ]);
    }
}
