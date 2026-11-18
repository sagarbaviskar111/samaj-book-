<?php

namespace App\Http\Controllers\LookingFor;

use App\Http\Controllers\Controller;
use App\Models\LookingForProfile;
use App\Support\BiodataOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LookingForController extends Controller
{
    public function create(Request $request)
    {
        if ($this->mine($request)) {
            return redirect()->route('looking-for.edit');
        }

        return view('looking-for.form', ['profile' => new LookingForProfile(['looking_for' => 'bride']), 'mode' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $this->mine($request) ?? new LookingForProfile();
        $profile->fill($this->data($request));
        $profile->user_id = $request->user()->id;
        $profile->save();

        return redirect()->route('biodata.index', ['for' => $profile->looking_for])
            ->with('status', 'धन्यवाद! अब आप सभी बायोडाटा पूरी जानकारी के साथ देख सकते हैं।');
    }

    public function edit(Request $request)
    {
        $profile = $this->mine($request);
        if (! $profile) {
            return redirect()->route('looking-for.create');
        }

        return view('looking-for.form', ['profile' => $profile, 'mode' => 'edit']);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->mine($request);
        abort_unless($profile, 404);
        $profile->update($this->data($request));

        return redirect()->route('biodata.index', ['for' => $profile->looking_for])->with('status', 'आपकी पसंद बदल दी गई।');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->mine($request)?->delete();

        return redirect()->route('biodata.index')->with('status', 'यह जानकारी हटा दी गई।');
    }

    private function mine(Request $request): ?LookingForProfile
    {
        return LookingForProfile::where("user_id", $request->user()->id)->first();
    }

    private function data(Request $request): array
    {
        $d = $request->validate([
            'for_whom' => ['required', Rule::in(array_keys(LookingForProfile::FOR_WHOM))],
            'looking_for' => ['required', Rule::in(['bride', 'groom'])],
            'age_min' => ['nullable', 'integer', 'min:18', 'max:80'],
            'age_max' => ['nullable', 'integer', 'min:18', 'max:80', 'gte:age_min'],
            'education_level' => ['nullable', Rule::in(array_keys(BiodataOptions::educationLevels()))],
            'district' => ['nullable', Rule::in(array_keys(BiodataOptions::districts()))],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        return array_map(fn ($v) => $v === '' ? null : $v, $d);
    }
}
