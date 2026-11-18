<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\BiodataOptions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Main admin only (route middleware admin.full): manage area sub-admins. */
class SubAdminController extends Controller
{
    private function find(int|string $id): User
    {
        return User::query()->where('role', 'subadmin')->findOrFail($id);
    }

    private function rules(?User $sub = null): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'digits:10', Rule::unique('users', 'phone')->ignore($sub?->id)],
            'password' => [$sub ? 'nullable' : 'required', Password::min(8)],
            'admin_area' => ['required', Rule::in(array_keys(BiodataOptions::districts()))],
            'status' => ['required', Rule::in(['active', 'blocked'])],
        ];
    }

    public function index()
    {
        $subs = User::query()->where('role', 'subadmin')->orderBy('admin_area')->orderBy('name')->get();

        return view('admin.subadmins.index', compact('subs'));
    }

    public function create()
    {
        return view('admin.subadmins.form', ['sub' => new User(['status' => 'active']), 'districts' => BiodataOptions::districts()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $sub = new User;
        $sub->forceFill($data + ['role' => 'subadmin', 'language' => 'hi'])->save();
        AuditLog::record('subadmin.create', 'user', $sub->id, $sub->name.' · '.$sub->admin_area);

        return redirect()->route('admin.subadmins.index')->with('status', __('Sub-admin created.'));
    }

    public function edit(int $id)
    {
        return view('admin.subadmins.form', ['sub' => $this->find($id), 'districts' => BiodataOptions::districts()]);
    }

    public function update(Request $request, int $id)
    {
        $sub = $this->find($id);
        $data = $request->validate($this->rules($sub));
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $sub->forceFill($data)->save();
        AuditLog::record('subadmin.update', 'user', $sub->id, $sub->name.' · '.$sub->admin_area.' · '.$sub->status);

        return redirect()->route('admin.subadmins.index')->with('status', __('Sub-admin updated.'));
    }

    public function destroy(int $id)
    {
        $sub = $this->find($id);
        AuditLog::record('subadmin.delete', 'user', $sub->id, $sub->name.' · '.$sub->admin_area);
        $sub->delete();

        return redirect()->route('admin.subadmins.index')->with('status', __('Sub-admin removed.'));
    }
}
