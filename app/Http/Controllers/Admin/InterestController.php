<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Services\Admin\AdminScope;
use Illuminate\Http\Request;

class InterestController extends Controller
{
    /** Who sent an interest request to whom, and which ones were accepted (connected). */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $base = fn () => AdminScope::interests(Interest::query());

        $interests = $base()
            ->with(['fromUser:id,name,phone', 'toUser:id,name,phone', 'fromBiodata:id,name,gender,dob,added_by,current_city,district', 'toBiodata:id,name,gender,dob,current_city,district'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()->paginate(20)->withQueryString();

        $counts = $base()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        $counts['all'] = $counts->sum();

        return view('admin.interests.index', compact('interests', 'status', 'counts'));
    }
}
