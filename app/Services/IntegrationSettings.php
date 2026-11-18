<?php

namespace App\Services;

use App\Models\IntegrationSecret;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Encrypted key/value store for third-party credentials (table integration_secrets).
 * Values are encrypted with Crypt::encryptString. Never log or render full secrets: use masked().
 */
class IntegrationSettings
{
    /**
     * Provider catalogue: group => [title, section, fields => [key => [label, secret?, type?, options?]]].
     * The "custom" group is free-form (superadmin adds arbitrary names).
     */
    public static function providers(): array
    {
        return [
            'razorpay' => ['title' => 'Razorpay', 'section' => 'Payments', 'fields' => [
                'key_id' => ['Key ID', false], 'key_secret' => ['Key secret', true], 'webhook_secret' => ['Webhook secret', true],
                'mode' => ['Mode', false, 'select', ['test', 'live']],
            ]],
            'phonepe' => ['title' => 'PhonePe', 'section' => 'Payments', 'fields' => [
                'merchant_id' => ['Merchant ID', false], 'salt_key' => ['Salt key', true], 'salt_index' => ['Salt index', false],
            ]],
            'stripe' => ['title' => 'Stripe', 'section' => 'Payments', 'fields' => [
                'publishable_key' => ['Publishable key', false], 'secret_key' => ['Secret key', true], 'webhook_secret' => ['Webhook secret', true],
            ]],
            'msg91' => ['title' => 'MSG91 (SMS)', 'section' => 'Messaging', 'fields' => [
                'auth_key' => ['Auth key', true], 'sender_id' => ['Sender ID', false], 'otp_template_id' => ['OTP template ID', false], 'notify_template_id' => ['Notification template ID', false],
            ]],
            'whatsapp' => ['title' => 'WhatsApp Business', 'section' => 'Messaging', 'fields' => [
                'token' => ['Access token', true], 'phone_number_id' => ['Phone number ID', false],
            ]],
        ];
    }

    public static function customGroup(): string
    {
        return 'custom';
    }

    public static function isSecretField(string $group, string $key): bool
    {
        return (bool) (static::providers()[$group]['fields'][$key][1] ?? true);
    }

    public static function get(string $group, string $key, ?string $default = null): ?string
    {
        $row = IntegrationSecret::query()->where('group', $group)->where('key', $key)->first();
        if (! $row || $row->value === null || $row->value === '') {
            return $default;
        }
        try {
            return Crypt::decryptString($row->value);
        } catch (DecryptException) {
            return $default;
        }
    }

    public static function has(string $group, string $key): bool
    {
        return static::get($group, $key) !== null;
    }

    public static function set(string $group, string $key, ?string $value, ?bool $isSecret = null, ?int $userId = null): void
    {
        $isSecret ??= static::isSecretField($group, $key);
        IntegrationSecret::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => ($value === null || $value === '') ? null : Crypt::encryptString($value), 'is_secret' => $isSecret, 'updated_by' => $userId ?? auth()->id()],
        );
    }

    public static function forget(string $group, string $key): void
    {
        IntegrationSecret::query()->where('group', $group)->where('key', $key)->delete();
    }

    /** Display-safe value: secrets become ••••••1234, plain values are returned as is. */
    public static function masked(string $group, string $key): ?string
    {
        $v = static::get($group, $key);
        if ($v === null) {
            return null;
        }
        $row = IntegrationSecret::query()->where('group', $group)->where('key', $key)->first();
        if (! ($row?->is_secret ?? true)) {
            return $v;
        }

        return static::mask($v);
    }

    public static function mask(string $v): string
    {
        return '••••••'.(mb_strlen($v) > 8 ? mb_substr($v, -4) : '');
    }

    /** Decrypted credentials of one provider/group for server-side use: ['key_id' => ..., ...]. */
    public static function forGateway(string $group): array
    {
        $out = [];
        foreach (IntegrationSecret::query()->where('group', $group)->get() as $row) {
            $v = static::get($group, $row->key);
            if ($v !== null) {
                $out[$row->key] = $v;
            }
        }

        return $out;
    }

    /** Safe, no-network sanity check of the key formats. Returns [ok, message]. */
    public static function formatCheck(string $group): array
    {
        $c = static::forGateway($group);
        $problems = [];
        switch ($group) {
            case 'razorpay':
                if (! preg_match('/^rzp_(test|live)_[A-Za-z0-9]{6,}$/', $c['key_id'] ?? '')) { $problems[] = 'Key ID should look like rzp_test_xxxx or rzp_live_xxxx'; }
                if (strlen($c['key_secret'] ?? '') < 10) { $problems[] = 'Key secret is missing or too short'; }
                if (isset($c['mode'], $c['key_id']) && ! str_contains($c['key_id'], '_'.$c['mode'].'_')) { $problems[] = 'Mode does not match the key prefix'; }
                break;
            case 'stripe':
                if (! preg_match('/^pk_(test|live)_/', $c['publishable_key'] ?? '')) { $problems[] = 'Publishable key should start with pk_test_ or pk_live_'; }
                if (! preg_match('/^sk_(test|live)_/', $c['secret_key'] ?? '')) { $problems[] = 'Secret key should start with sk_test_ or sk_live_'; }
                break;
            case 'phonepe':
                if (blank($c['merchant_id'] ?? null)) { $problems[] = 'Merchant ID is missing'; }
                if (strlen($c['salt_key'] ?? '') < 10) { $problems[] = 'Salt key is missing or too short'; }
                if (! ctype_digit((string) ($c['salt_index'] ?? ''))) { $problems[] = 'Salt index should be a number'; }
                break;
            case 'msg91':
                if (strlen($c['auth_key'] ?? '') < 10) { $problems[] = 'Auth key is missing or too short'; }
                break;
            case 'whatsapp':
                if (strlen($c['token'] ?? '') < 20) { $problems[] = 'Token is missing or too short'; }
                if (! ctype_digit((string) ($c['phone_number_id'] ?? ''))) { $problems[] = 'Phone number ID should be numeric'; }
                break;
            default:
                return [true, 'No format rules for this group.'];
        }

        return $problems ? [false, implode('; ', $problems)] : [true, 'Format looks valid (no network call was made).'];
    }
}
