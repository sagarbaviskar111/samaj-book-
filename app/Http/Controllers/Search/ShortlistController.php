<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use App\Models\Interest;
use App\Models\Shortlist;
use Illuminate\Http\Request;

class ShortlistController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $biodatas = Biodata::approved()
            ->join('shortlists', 'shortlists.biodata_id', '=', 'biodatas.id')
            ->where('shortlists.user_id', $user->id)
            ->select('biodatas.*')
            ->with('primaryPhoto')
            ->orderByDesc('shortlists.id')
            ->paginate(12);

        return view('search.shortlist', [
            'biodatas' => $biodatas,
            'shortlistIds' => $biodatas->pluck('id')->all(),
            'interestMap' => BiodataSearchController::interestMapFor($user->id, $biodatas->pluck('id')->all()),
            'matches' => [],
        ]);
    }

    public function toggle(Request $request, Biodata $biodata)
    {
        $user = $request->user();
        abort_if(\App\Support\AccessLevel::isLite($user), 403);
        abort_unless($biodata->status === 'approved' && $biodata->user_id !== $user->id, 404);

        $existing = Shortlist::where('user_id', $user->id)->where('biodata_id', $biodata->id)->first();
        if ($existing) {
            $existing->delete();
            $on = false;
        } else {
            Shortlist::create(['user_id' => $user->id, 'biodata_id' => $biodata->id]);
            $on = true;
        }

        if ($request->expectsJson()) {
            return response()->json(['shortlisted' => $on]);
        }

        return back()->with('status', $on ? __('सहेजे हुए रिश्तों में जोड़ दिया गया।') : __('सहेजे हुए रिश्तों से हटा दिया गया।'));
    }
}
