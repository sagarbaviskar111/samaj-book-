<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkApproveRequest;
use App\Http\Requests\Admin\ReasonRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\Admin\AdminNotifier;
use App\Services\Admin\AdminScope;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private function find(int|string $id): User
    {
        return AdminScope::users(User::query())->findOrFail($id);
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $q = trim((string) $request->query('q', ''));
        $area = trim((string) $request->query('area', ''));

        $base = AdminScope::users(User::query());
        $counts = (clone $base)->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $users = (clone $base)
            ->when(in_array($status, ['pending', 'active', 'rejected', 'blocked'], true), fn ($x) => $x->where('status', $status))
            ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")))
            ->when($area !== '', fn ($x) => $x->where('area', $area))
            ->latest()->paginate(20)->withQueryString();

        $areas = (clone $base)->whereNotNull('area')->distinct()->orderBy('area')->pluck('area');

        return view('admin.users.index', compact('users', 'counts', 'status', 'q', 'area', 'areas'));
    }

    public function show(int $id)
    {
        $user = $this->find($id)->loadCount(['biodatas', 'posts']);
        $biodatas = $user->biodatas()->latest()->get();
        $logs = AuditLog::visibleToAdmins()->where('target_type', 'user')->where('target_id', $user->id)->with('admin:id,name')->latest()->limit(10)->get();

        return view('admin.users.show', compact('user', 'biodatas', 'logs'));
    }

    private function doApprove(User $u): void
    {
        $u->status = 'active';
        $u->reject_reason = null;
        $u->save();
        AdminNotifier::to($u, 'account_approved', 'Your account is approved', 'Welcome to the samaj! You can now use the app.', AdminNotifier::routeOrNull('home'));
        AuditLog::record('user.approve', 'user', $u->id, $u->name);
    }

    public function approve(int $id)
    {
        $this->doApprove($this->find($id));

        return back()->with('status', __('Member approved.'));
    }

    public function bulkApprove(BulkApproveRequest $request)
    {
        $n = 0;
        AdminScope::users(User::query())->whereIn('id', $request->validated('ids'))->where('status', 'pending')
            ->get()->each(function (User $u) use (&$n) {
                $this->doApprove($u);
                $n++;
            });

        return back()->with('status', __(':n members approved.', ['n' => $n]));
    }

    public function reject(ReasonRequest $request, int $id)
    {
        $u = $this->find($id);
        $u->status = 'rejected';
        $u->reject_reason = $request->validated('reason');
        $u->save();
        AdminNotifier::to($u, 'account_rejected', 'Your account was not approved', $u->reject_reason, AdminNotifier::routeOrNull('account.status'));
        AuditLog::record('user.reject', 'user', $u->id, $u->reject_reason);

        return back()->with('status', __('Member rejected.'));
    }

    public function block(int $id)
    {
        $u = $this->find($id);
        $u->status = 'blocked';
        $u->save();
        AuditLog::record('user.block', 'user', $u->id, $u->name);

        return back()->with('status', __('Member blocked.'));
    }

    public function unblock(int $id)
    {
        $u = $this->find($id);
        $u->status = 'active';
        $u->save();
        AuditLog::record('user.unblock', 'user', $u->id, $u->name);

        return back()->with('status', __('Member unblocked.'));
    }

    public function resetPassword(int $id)
    {
        $u = $this->find($id);
        $temp = Str::password(10, symbols: false);
        $u->password = $temp;
        $u->save();
        AuditLog::record('user.reset_password', 'user', $u->id, $u->name);

        return back()->with('temp_password', $temp)->with('status', __('Temporary password generated. Shown once.'));
    }

    public function destroy(int $id)
    {
        $u = $this->find($id);
        AuditLog::record('user.delete', 'user', $u->id, $u->name.' ('.$u->phone.')');
        $u->delete();

        return redirect()->route('admin.users.index')->with('status', __('Member deleted.'));
    }
}
