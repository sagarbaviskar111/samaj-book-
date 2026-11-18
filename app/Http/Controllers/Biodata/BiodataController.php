<?php

namespace App\Http\Controllers\Biodata;

use App\Http\Controllers\Controller;
use App\Http\Requests\Biodata\BiodataRequest;
use App\Models\Biodata;
use App\Support\AccessLevel;
use App\Support\BiodataAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BiodataController extends Controller
{
    public const MAX_PER_USER = 5;

    public function mine(Request $request)
    {
        $biodatas = $request->user()->biodatas()->with('primaryPhoto')->latest()->get();

        return view('biodata.mine', ['biodatas' => $biodatas, 'canAdd' => $biodatas->count() < self::MAX_PER_USER]);
    }

    public function create(Request $request)
    {
        if ($request->user()->biodatas()->count() >= self::MAX_PER_USER) {
            return redirect()->route('biodata.mine')->with('status', 'आप ज़्यादा से ज़्यादा '.self::MAX_PER_USER.' बायोडाटा बना सकते हैं।');
        }

        return view('biodata.create', ['biodata' => new Biodata(['added_by' => 'self', 'marital_status' => 'never_married', 'photo_privacy' => 'members', 'income_private' => true])]);
    }

    public function store(BiodataRequest $request): RedirectResponse
    {
        abort_if($request->user()->biodatas()->count() >= self::MAX_PER_USER, 403);

        $biodata = new Biodata($request->biodataData());
        $biodata->user_id = $request->user()->id;
        $biodata->status = $request->isDraft() ? 'draft' : 'pending';
        $biodata->save();

        return redirect()->route('biodata.edit', [$biodata, 'step' => 'photos'])
            ->with('status', $request->isDraft() ? 'ड्राफ़्ट सेव हो गया। अब फ़ोटो जोड़िए।' : 'बायोडाटा मंज़ूरी के लिए भेज दिया गया। इसे पूरा करने के लिए फ़ोटो जोड़िए।');
    }

    public function show(Request $request, Biodata $biodata)
    {
        $user = $request->user();
        abort_unless(BiodataAccess::canView($user, $biodata), 404);

        if (! BiodataAccess::isOwner($user, $biodata) && AccessLevel::isLite($user)) {
            // Limited mode: only photo + first name + age are ever passed to the view.
            $biodata->load('primaryPhoto');

            return view('biodata.show-lite', [
                'biodata' => $biodata,
                'first' => explode(' ', trim($biodata->name))[0] ?? $biodata->name,
                'age' => $biodata->dob->age,
                'photoMode' => BiodataAccess::photoMode($user, $biodata),
            ]);
        }

        $biodata->load('photos');
        if (! BiodataAccess::isOwner($user, $biodata)) {
            Biodata::whereKey($biodata->id)->increment('views');
        }

        return view('biodata.show', [
            'biodata' => $biodata,
            'isOwner' => BiodataAccess::isOwner($user, $biodata),
            'isAdmin' => BiodataAccess::isAdmin($user),
            'canContact' => BiodataAccess::canSeeContact($user, $biodata),
            'canIncome' => BiodataAccess::canSeeIncome($user, $biodata),
            'canPdf' => BiodataAccess::canDownloadPdf($user, $biodata),
            'canKundli' => \App\Support\KundliAccess::canSeeProfileValues($user, $biodata),
            'kundliInterest' => BiodataAccess::isOwner($user, $biodata) ? null : \App\Support\KundliAccess::interestFor($user, $biodata),
            'photoMode' => BiodataAccess::photoMode($user, $biodata),
        ]);
    }

    public function edit(Request $request, Biodata $biodata)
    {
        abort_unless(BiodataAccess::canManage($request->user(), $biodata), 403);

        return view('biodata.edit', ['biodata' => $biodata->load('photos')]);
    }

    /**
     * Status rules: draft/rejected + submit => pending. Approved stays approved unless
     * name, dob or gender changed (photo uploads also re-queue, see PhotoController).
     */
    public function update(BiodataRequest $request, Biodata $biodata): RedirectResponse
    {
        abort_unless(BiodataAccess::canManage($request->user(), $biodata), 403);

        $biodata->fill($request->biodataData());
        $sensitive = $biodata->isDirty(['name', 'dob', 'gender']);

        if ($request->isDraft()) {
            if (in_array($biodata->status, ['draft'], true)) {
                $biodata->status = 'draft';
            }
        } elseif (in_array($biodata->status, ['draft', 'rejected'], true)) {
            $biodata->status = 'pending';
        } elseif ($biodata->status === 'approved' && $sensitive) {
            $biodata->status = 'pending';
            $biodata->verified = false;
        }
        $biodata->save();

        return redirect()->route('biodata.mine')->with('status', $biodata->status === 'pending'
            ? 'सेव हो गया। आपका बायोडाटा एडमिन की मंज़ूरी का इंतज़ार कर रहा है।'
            : 'बायोडाटा अपडेट हो गया।');
    }

    /** Owner actions: pause | resume | married */
    public function changeStatus(Request $request, Biodata $biodata): RedirectResponse
    {
        abort_unless(BiodataAccess::canManage($request->user(), $biodata), 403);
        $action = $request->validate(['action' => ['required', 'in:pause,resume,married']])['action'];

        if ($action === 'pause' && $biodata->status === 'approved') {
            $biodata->status = 'paused';
        } elseif ($action === 'resume' && $biodata->status === 'paused') {
            $biodata->status = 'approved';
        } elseif ($action === 'married' && in_array($biodata->status, ['approved', 'paused', 'pending'], true)) {
            $biodata->status = 'married';
        } else {
            return back()->with('status', 'इस बायोडाटा पर यह काम अभी नहीं हो सकता।');
        }
        $biodata->save();

        return back()->with('status', 'बायोडाटा की स्थिति बदल गई।');
    }

    public function destroy(Request $request, Biodata $biodata): RedirectResponse
    {
        abort_unless(BiodataAccess::canManage($request->user(), $biodata), 403);

        Storage::disk('public')->deleteDirectory("biodata/{$biodata->id}");
        $biodata->delete();

        return redirect()->route('biodata.mine')->with('status', 'बायोडाटा हटा दिया गया।');
    }
}
