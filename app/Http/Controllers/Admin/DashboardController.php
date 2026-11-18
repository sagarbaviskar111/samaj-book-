<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Biodata;
use App\Models\Interest;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Services\Admin\AdminScope;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $members = fn () => AdminScope::users(User::query());
        $bio = fn () => AdminScope::biodatas(Biodata::query());
        $ids = fn () => AdminScope::users(User::query())->select('id');
        $interests = fn () => AdminScope::area() ? Interest::query()->whereIn('to_user_id', $ids()) : Interest::query();
        $posts = fn () => AdminScope::area() ? Post::query()->whereIn('user_id', $ids()) : Post::query();

        $stats = [
            'members' => $members()->count(),
            'pending_members' => $members()->where('status', 'pending')->count(),
            'pending_biodata' => $bio()->where('status', 'pending')->count(),
            'approved_biodata' => $bio()->where('status', 'approved')->count(),
            'open_reports' => AdminScope::reports(Report::query())->where('status', 'open')->count(),
            'interests_sent' => $interests()->where('created_at', '>=', now()->startOfMonth())->count(),
            'interests_accepted' => $interests()->where('status', 'accepted')->where('responded_at', '>=', now()->startOfMonth())->count(),
            'posts_week' => $posts()->where('created_at', '>=', now()->startOfWeek())->count(),
        ];

        // registrations in the last 14 days (grouped in PHP to stay DB-agnostic)
        $start = now()->subDays(13)->startOfDay();
        $byDay = $members()->where('created_at', '>=', $start)->pluck('created_at')
            ->countBy(fn ($d) => $d->format('Y-m-d'));
        $series = collect(range(0, 13))->map(function ($i) use ($start, $byDay) {
            $d = $start->copy()->addDays($i);

            return ['label' => $d->format('d M'), 'short' => $d->format('d'), 'count' => (int) ($byDay[$d->format('Y-m-d')] ?? 0)];
        });

        $activity = AuditLog::visibleToAdmins()->with('admin:id,name,admin_area')->when(AdminScope::area(), fn ($q) => $q->where('admin_id', auth()->id()))->latest()->limit(8)->get();

        return view('admin.dashboard', compact('stats', 'series', 'activity'));
    }
}
