<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\IntegrationSecret;
use App\Models\Setting;
use App\Services\IntegrationSettings as IS;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Secrets are write-only from the UI: never rendered in full, never logged. */
class IntegrationController extends Controller
{
    public function index()
    {
        $providers = IS::providers();
        $values = [];
        foreach ($providers as $group => $def) {
            foreach ($def['fields'] as $key => $f) {
                $values[$group][$key] = IS::masked($group, $key);
            }
        }
        $custom = IntegrationSecret::query()->where('group', IS::customGroup())->orderBy('key')->get()
            ->map(fn ($r) => ['key' => $r->key, 'masked' => IS::masked(IS::customGroup(), $r->key), 'secret' => $r->is_secret, 'updated_at' => $r->updated_at]);
        $push = [
            'public' => env('VAPID_PUBLIC_KEY'), // shown in full: public by design
            'private' => filled(env('VAPID_PRIVATE_KEY')) ? IS::mask((string) env('VAPID_PRIVATE_KEY')) : null,
            'subject' => env('VAPID_SUBJECT'),
        ];
        $config = [
            'currency' => Setting::get('payments.currency', 'INR'),
            'enabled' => (bool) Setting::get('payments.enabled', false),
            'default_gateway' => Setting::get('payments.default_gateway', 'razorpay'),
            'platform_fee_text' => Setting::get('payments.platform_fee_text', ''),
        ];

        return view('superadmin.integrations.index', compact('providers', 'values', 'custom', 'push', 'config'));
    }

    public function update(Request $request, string $group)
    {
        $providers = IS::providers();
        abort_unless(isset($providers[$group]), 404);
        $changed = [];
        foreach ($providers[$group]['fields'] as $key => $f) {
            $rules = isset($f[3]) ? ['nullable', Rule::in($f[3])] : ['nullable', 'string', 'max:500'];
            $val = $request->validate([$key => $rules])[$key] ?? null;
            if (blank($val)) {
                continue; // blank keeps the stored value
            }
            IS::set($group, $key, trim($val), (bool) $f[1], $request->user()->id);
            $changed[] = $key;
        }
        if ($changed) {
            AuditLog::recordSa('integration.update', 'integration', null, 'updated '.implode(', ', array_map(fn ($k) => "$group.$k", $changed)));
        }

        return back()->with('status', $changed ? __('Saved.') : __('Nothing changed.'));
    }

    public function check(string $group)
    {
        abort_unless(isset(IS::providers()[$group]), 404);
        [$ok, $msg] = IS::formatCheck($group);

        return back()->with($ok ? 'status' : 'check_error', ucfirst($group).': '.$msg);
    }

    public function storeCustom(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9_.\- ]+$/'],
            'value' => ['required', 'string', 'max:2000'],
        ]);
        $name = trim($data['name']);
        IS::set(IS::customGroup(), $name, $data['value'], true, $request->user()->id);
        AuditLog::recordSa('integration.update', 'integration', null, 'updated custom.'.$name);

        return back()->with('status', __('Saved.'));
    }

    public function destroyCustom(string $key)
    {
        abort_unless(IntegrationSecret::query()->where('group', IS::customGroup())->where('key', $key)->exists(), 404);
        IS::forget(IS::customGroup(), $key);
        AuditLog::recordSa('integration.delete', 'integration', null, 'removed custom.'.$key);

        return back()->with('status', __('Removed.'));
    }

    public function paymentConfig(Request $request)
    {
        $data = $request->validate([
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'enabled' => ['nullable', 'boolean'],
            'default_gateway' => ['required', Rule::in(['razorpay', 'phonepe', 'stripe', 'manual'])],
            'platform_fee_text' => ['nullable', 'string', 'max:300'],
        ]);
        Setting::put('payments.currency', strtoupper($data['currency']));
        Setting::put('payments.enabled', $request->boolean('enabled') ? 1 : 0);
        Setting::put('payments.default_gateway', $data['default_gateway']);
        Setting::put('payments.platform_fee_text', $data['platform_fee_text'] ?? '');
        AuditLog::recordSa('integration.payment-config', 'settings', null, 'enabled='.($request->boolean('enabled') ? 'yes' : 'no').' gateway='.$data['default_gateway']);

        return back()->with('status', __('Payment settings saved.'));
    }
}
