<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReasonRequest;
use App\Models\AuditLog;
use App\Models\Biodata;
use App\Services\Admin\AdminNotifier;
use App\Services\Admin\AdminScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BiodataController extends Controller
{
    private function find(int|string $id): Biodata
    {
        return AdminScope::biodatas(Biodata::query())->with('user')->findOrFail($id);
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $gender = $request->query('gender');
        $q = trim((string) $request->query('q', ''));
        $district = trim((string) $request->query('district', ''));

        $base = AdminScope::biodatas(Biodata::query());
        $counts = (clone $base)->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $items = (clone $base)->with(['user:id,name,phone', 'primaryPhoto'])
            ->when($status !== 'all', fn ($x) => $x->where('status', $status))
            ->when(in_array($gender, ['male', 'female'], true), fn ($x) => $x->where('gender', $gender))
            ->when($district !== '', fn ($x) => $x->where('district', 'like', "%{$district}%"))
            ->when($q !== '', fn ($x) => $x->where('name', 'like', "%{$q}%"))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()->paginate(15)->withQueryString();

        return view('admin.biodata.index', compact('items', 'counts', 'status', 'gender', 'q', 'district'));
    }

    public function show(int $id)
    {
        $b = $this->find($id)->load('photos');
        $checks = [
            'Name and DOB present' => filled($b->name) && $b->dob,
            'At least one photo' => $b->photos->isNotEmpty(),
            'Contact phone given' => filled($b->contact_phone),
            'Parents named' => filled($b->father_name) || filled($b->mother_name),
            'Gotra and district filled' => filled($b->gotra) && filled($b->district),
            'About / expectation written' => filled($b->about),
            'Name matches account holder' => $b->added_by !== 'self' || strcasecmp(trim($b->name), trim($b->user->name)) === 0,
        ];

        return view('admin.biodata.show', compact('b', 'checks'));
    }

    public function approve(int $id)
    {
        $b = $this->find($id);
        $b->status = 'approved';
        $b->reject_reason = null;
        $b->approved_at = now();
        $b->approved_by = auth()->id();
        $b->save();

        AdminNotifier::to($b->user, 'biodata_approved', 'Your biodata is approved', 'It is now visible to members.', AdminNotifier::routeOrNull('biodata.index'));
        AuditLog::record('biodata.approve', 'biodata', $b->id, $b->name);

        if (class_exists(\App\Services\NewBiodataAlert::class)) {
            try {
                app(\App\Services\NewBiodataAlert::class)->handle($b);
            } catch (\Throwable $e) {
                report($e); // alerts must never block approval
            }
        }

        return back()->with('status', __('Biodata approved.'));
    }

    public function reject(ReasonRequest $request, int $id)
    {
        $b = $this->find($id);
        $b->status = 'rejected';
        $b->reject_reason = $request->validated('reason');
        $b->save();

        AdminNotifier::to($b->user, 'biodata_rejected', 'Your biodata needs changes', $b->reject_reason, AdminNotifier::routeOrNull('biodata.index'));
        AuditLog::record('biodata.reject', 'biodata', $b->id, $b->reject_reason);

        return back()->with('status', __('Biodata rejected.'));
    }

    public function toggleVerified(int $id)
    {
        $b = $this->find($id);
        $b->verified = ! $b->verified;
        $b->save();
        AuditLog::record($b->verified ? 'biodata.verify' : 'biodata.unverify', 'biodata', $b->id, $b->name);

        return back()->with('status', $b->verified ? __('Marked verified.') : __('Verified badge removed.'));
    }

    public function setStatus(Request $request, int $id)
    {
        $data = $request->validate(['status' => ['required', 'in:approved,paused,married']]);
        $b = $this->find($id);
        $b->status = $data['status'];
        $b->save();
        AuditLog::record('biodata.status', 'biodata', $b->id, $data['status']);

        return back()->with('status', __('Status updated.'));
    }

    public function destroy(int $id)
    {
        $b = $this->find($id)->load('photos');
        foreach ($b->photos as $p) {
            Storage::disk('public')->delete($p->path);
        }
        AuditLog::record('biodata.delete', 'biodata', $b->id, $b->name);
        $b->delete();

        return redirect()->route('admin.biodata.index')->with('status', __('Biodata deleted.'));
    }
}
