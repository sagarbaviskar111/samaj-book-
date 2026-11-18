<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Services\Kundli\GunMilan;
use App\Support\KundliAccess;
use Illuminate\Http\Request;

class KundliController extends Controller
{
    public function show(Request $request, Interest $interest)
    {
        $user = $request->user();
        abort_unless(KundliAccess::canOpenInterest($user, $interest), 403);

        $interest->load(['fromBiodata', 'toBiodata']);
        abort_unless($interest->fromBiodata && $interest->toBiodata, 404);

        $a = $interest->fromBiodata;
        $b = $interest->toBiodata;
        abort_unless(KundliAccess::canSeeKundli($user, $a, $b), 403);

        $boy = ($a->gender === 'female' && $b->gender === 'male') ? $b : $a;
        $girl = $boy->is($a) ? $b : $a;

        return view('search.kundli', [
            'interest' => $interest,
            'boy' => $boy,
            'girl' => $girl,
            'result' => GunMilan::forBiodatas($a, $b),
            'uid' => $user->id,
        ]);
    }
}
