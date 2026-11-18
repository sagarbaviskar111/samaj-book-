<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Superadmin creates/manages users with role "admin". A superadmin can never be created or touched here. */
class AdminController extends Controller
{
    private function find(int|string $id): User
    {
        return User::query()->where('role', 'admin')->findOrFail($id);
    }

    private function rules(?User $a = null): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'digits:10', Rule::unique('users', 'phone')->ignore($a?->id)],
            'email' => ['nullable', 'email', 'max:190', Rule::unique('users', 'email')->ignore($a?->id)],
            'password' => [$a ? 'nullable' : 'required', Password::min(8)],
        ];
    }

    public function index()
    {
        $admins = User::query()->where('role', 'admin')->orderBy('name')->get();
        $lastLogin = \App\Models\LoginLog::query()->whereIn('user_id', $admins->pluck('id'))->where('event', 'login')
            ->selectRaw('user_id, max(created_at) as at')->groupBy('user_id')->pluck('at', 'user_id');

        return view('superadmin.admins.index', compact('admins', 'lastLogin'));
    }

    public function create()
    {
        return view('superadmin.admins.form', ['adm' => new User(['status' => 'active'])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $a = new User;
        $a->forceFill($data + ['role' => 'admin', 'status' => 'active', 'language' => 'hi'])->save();
        AuditLog::recordSa('admin.create', 'user', $a->id, $a->name.' · '.$a->phone);

        return redirect()->route('superadmin.admins.index')->with('status', __('Admin created.'));
    }

    public function edit(int $id)
    {
        return view('superadmin.admins.form', ['adm' => $this->find($id)]);
    }

    public function update(Request $request, int $id)
    {
        $a = $this->find($id);
        $data = $request->validate($this->rules($a));
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $a->forceFill($data)->save();
        AuditLog::recordSa('admin.update', 'user', $a->id, $a->name.' · '.$a->phone.(isset($data['password']) ? ' · password changed' : ''));

        return redirect()->route('superadmin.admins.index')->with('status', __('Admin updated.'));
    }

    public function block(int $id)
    {
        $a = $this->find($id);
        $a->forceFill(['status' => 'blocked'])->save();
        AuditLog::recordSa('admin.block', 'user', $a->id, $a->name);

        return back()->with('status', __('Admin blocked.'));
    }

    public function unblock(int $id)
    {
        $a = $this->find($id);
        $a->forceFill(['status' => 'active'])->save();
        AuditLog::recordSa('admin.unblock', 'user', $a->id, $a->name);

        return back()->with('status', __('Admin unblocked.'));
    }

    public function resetPassword(int $id)
    {
        $a = $this->find($id);
        $temp = Str::password(10, symbols: false);
        $a->forceFill(['password' => $temp])->save();
        AuditLog::recordSa('admin.reset-password', 'user', $a->id, $a->name); // never log the password

        return back()->with('status', __('Password reset.'))->with('temp_password', $temp);
    }

    public function destroy(Request $request, int $id)
    {
        $a = $this->find($id);
        abort_if($a->id === $request->user()->id, 403);
        AuditLog::recordSa('admin.delete', 'user', $a->id, $a->name.' · '.$a->phone);
        $a->delete();

        return redirect()->route('superadmin.admins.index')->with('status', __('Admin removed.'));
    }
}
