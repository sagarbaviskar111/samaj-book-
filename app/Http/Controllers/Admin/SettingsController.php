<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Models\AuditLog;
use App\Models\Setting;

class SettingsController extends Controller
{
    private const KEYS = ['site_name', 'site_tagline', 'interest_daily_limit', 'contact_email', 'contact_phone', 'auto_approve'];

    public function edit()
    {
        $s = [
            'site_name' => Setting::get('site_name', config('app.name')),
            'site_tagline' => Setting::get('site_tagline', ''),
            'interest_daily_limit' => Setting::get('interest_daily_limit', 5),
            'contact_email' => Setting::get('contact_email', ''),
            'contact_phone' => Setting::get('contact_phone', ''),
            'auto_approve' => (bool) Setting::get('auto_approve', false),
        ];

        return view('admin.settings', compact('s'));
    }

    public function update(SettingsRequest $request)
    {
        $data = $request->validated();
        foreach (self::KEYS as $k) {
            Setting::put($k, is_bool($data[$k] ?? null) ? (int) $data[$k] : ($data[$k] ?? ''));
        }
        AuditLog::record('settings.update', 'settings', null, 'limit='.$data['interest_daily_limit']);

        return back()->with('status', __('Settings saved.'));
    }

    public function audit()
    {
        $logs = AuditLog::visibleToAdmins()->with('admin:id,name,admin_area')->latest()->paginate(30);

        return view('admin.audit', compact('logs'));
    }
}
