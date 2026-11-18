<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LoginLog;
use App\Models\Payment;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'admins' => User::where('role', 'admin')->count(),
            'admins_blocked' => User::where('role', 'admin')->where('status', 'blocked')->count(),
            'subadmins' => User::where('role', 'subadmin')->count(),
            'members' => User::where('role', 'user')->count(),
            'paid_month' => (int) Payment::where('status', 'paid')->where('paid_at', '>=', now()->startOfMonth())->sum('amount_paise'),
            'failed_24h' => LoginLog::where('event', 'failed')->where('created_at', '>=', now()->subDay())->count(),
        ];
        $recent = AuditLog::with('admin:id,name,role')->latest('id')->limit(10)->get();

        return view('superadmin.dashboard', compact('stats', 'recent'));
    }
}
