<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LoginLog;
use App\Support\CsvExport;
use Illuminate\Http\Request;

class LogController extends Controller
{
    private function auditQuery(Request $r)
    {
        $actor = trim((string) $r->query('actor', ''));
        $action = trim((string) $r->query('action', ''));
        $type = trim((string) $r->query('type', ''));

        return AuditLog::query()->with('admin:id,name,role,admin_area')
            ->when($actor !== '', fn ($q) => $q->whereIn('admin_id', \App\Models\User::query()->where('name', 'like', "%{$actor}%")->orWhere('phone', 'like', "%{$actor}%")->select('id')))
            ->when($action !== '', fn ($q) => $q->where('action', 'like', "%{$action}%"))
            ->when($type !== '', fn ($q) => $q->where('target_type', $type))
            ->when($r->query('from'), fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($r->query('to'), fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest('id');
    }

    public function audit(Request $request)
    {
        $logs = $this->auditQuery($request)->paginate(40)->withQueryString();
        $types = AuditLog::query()->whereNotNull('target_type')->distinct()->orderBy('target_type')->pluck('target_type');

        return view('superadmin.logs.audit', compact('logs', 'types'));
    }

    public function auditExport(Request $request)
    {
        $rows = (function () use ($request) {
            foreach ($this->auditQuery($request)->limit(20000)->cursor() as $l) {
                yield [$l->id, $l->created_at, $l->admin?->name, $l->admin?->role, $l->action, $l->target_type, $l->target_id, $l->note];
            }
        })();

        return CsvExport::stream('audit-log-'.now()->format('Ymd-His').'.csv', ['id', 'at', 'actor', 'actor_role', 'action', 'target_type', 'target_id', 'note'], $rows);
    }

    private function loginQuery(Request $r)
    {
        $event = (string) $r->query('event', '');
        $q = trim((string) $r->query('q', ''));

        return LoginLog::query()->with('user:id,name,role')
            ->when(in_array($event, ['login', 'failed', 'logout', 'dev_login'], true), fn ($x) => $x->where('event', $event))
            ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w->where('phone', 'like', "%{$q}%")->orWhere('ip', 'like', "%{$q}%")))
            ->when($r->query('from'), fn ($x, $d) => $x->whereDate('created_at', '>=', $d))
            ->when($r->query('to'), fn ($x, $d) => $x->whereDate('created_at', '<=', $d))
            ->latest('id');
    }

    public function logins(Request $request)
    {
        $logs = $this->loginQuery($request)->paginate(40)->withQueryString();

        return view('superadmin.logs.logins', compact('logs'));
    }

    public function loginsExport(Request $request)
    {
        $rows = (function () use ($request) {
            foreach ($this->loginQuery($request)->limit(20000)->cursor() as $l) {
                yield [$l->id, $l->created_at, $l->event, $l->phone, $l->user?->name, $l->ip, $l->user_agent];
            }
        })();

        return CsvExport::stream('login-log-'.now()->format('Ymd-His').'.csv', ['id', 'at', 'event', 'phone', 'user', 'ip', 'user_agent'], $rows);
    }

    public function security()
    {
        $since = now()->subDay();
        $failed = fn () => LoginLog::query()->where('event', 'failed')->where('created_at', '>=', $since);
        $byIp = $failed()->selectRaw('ip, count(*) as attempts, count(distinct phone) as phones, max(created_at) as last_at')
            ->groupBy('ip')->orderByDesc('attempts')->limit(25)->get();
        $byPhone = $failed()->selectRaw('phone, count(*) as attempts, count(distinct ip) as ips, max(created_at) as last_at')
            ->groupBy('phone')->orderByDesc('attempts')->limit(25)->get();
        $total = $failed()->count();

        return view('superadmin.logs.security', compact('byIp', 'byPhone', 'total'));
    }
}
