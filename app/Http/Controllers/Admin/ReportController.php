<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Biodata;
use App\Models\Comment;
use App\Models\Interest;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Services\Admin\AdminScope;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function find(int|string $id): Report
    {
        return AdminScope::reports(Report::query())->findOrFail($id);
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'open');
        $reports = AdminScope::reports(Report::query())->with('reporter:id,name,phone')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()->paginate(15)->withQueryString();

        // bulk-load targets by type to avoid N+1
        $ids = $reports->getCollection()->groupBy('target_type')->map(fn ($g) => $g->pluck('target_id')->unique()->all());
        $targets = [
            'post' => Post::with('user:id,name,phone,status')->whereIn('id', $ids['post'] ?? [])->get()->keyBy('id'),
            'biodata' => Biodata::with('user:id,name,phone,status')->whereIn('id', $ids['biodata'] ?? [])->get()->keyBy('id'),
            'user' => User::whereIn('id', $ids['user'] ?? [])->get()->keyBy('id'),
            'comment' => Comment::with('user:id,name,phone,status')->whereIn('id', $ids['comment'] ?? [])->get()->keyBy('id'),
            'interest_help' => Interest::with(['fromUser:id,name,phone', 'toUser:id,name,phone', 'fromBiodata:id,name,contact_phone', 'toBiodata:id,name,contact_phone'])
                ->whereIn('id', $ids['interest_help'] ?? [])->get()->keyBy('id'),
        ];

        $counts = AdminScope::reports(Report::query())->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('admin.reports.index', compact('reports', 'targets', 'status', 'counts'));
    }

    private function close(Report $r, string $status, string $action, ?string $note = null): void
    {
        $r->update(['status' => $status]);
        AuditLog::record($action, 'report', $r->id, $note ?? $r->target_type.'#'.$r->target_id);
    }

    public function resolve(int $id)
    {
        $this->close($this->find($id), 'resolved', 'report.resolve');

        return back()->with('status', __('Report resolved.'));
    }

    public function dismiss(int $id)
    {
        $this->close($this->find($id), 'dismissed', 'report.dismiss');

        return back()->with('status', __('Report dismissed.'));
    }

    public function hidePost(int $id)
    {
        $r = $this->find($id);
        abort_unless($r->target_type === 'post', 422);
        Post::whereKey($r->target_id)->update(['status' => 'hidden']);
        $this->close($r, 'resolved', 'report.hide_post', 'post#'.$r->target_id);

        return back()->with('status', __('Post hidden.'));
    }

    public function blockUser(int $id)
    {
        $r = $this->find($id);
        $userId = match ($r->target_type) {
            'user' => $r->target_id,
            'post' => Post::whereKey($r->target_id)->value('user_id'),
            'comment' => Comment::whereKey($r->target_id)->value('user_id'),
            'biodata' => Biodata::whereKey($r->target_id)->value('user_id'),
            default => null,
        };
        abort_unless($userId, 422, 'No user to block');

        $u = AdminScope::users(User::query())->findOrFail($userId); // never admins
        $u->status = 'blocked';
        $u->save();
        AuditLog::record('user.block', 'user', $u->id, 'via report #'.$r->id);
        $this->close($r, 'resolved', 'report.block_user', 'user#'.$u->id);

        return back()->with('status', __('User blocked.'));
    }
}
