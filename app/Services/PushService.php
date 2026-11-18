<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/** Sends Web Push. Silently no-ops when VAPID keys are not configured. */
class PushService
{
    public static function configured(): bool
    {
        return (bool) (env('VAPID_PUBLIC_KEY') && env('VAPID_PRIVATE_KEY'));
    }

    public static function toUser(User $u, string $title, string $body, ?string $url = null, ?string $type = null): void
    {
        if (! self::configured()) {
            return;
        }
        if ($type !== null && ! NotificationPrefs::allows($u, $type)) {
            return;
        }
        $subs = PushSubscription::where('user_id', $u->id)->get();
        if ($subs->isEmpty()) {
            return;
        }

        try {
            $webPush = new WebPush(['VAPID' => [
                'subject' => env('VAPID_SUBJECT', config('app.url')),
                'publicKey' => env('VAPID_PUBLIC_KEY'),
                'privateKey' => env('VAPID_PRIVATE_KEY'),
            ]]);
            $payload = json_encode(['title' => $title, 'body' => $body, 'url' => $url ?: url('/home')]);

            foreach ($subs as $s) {
                $webPush->queueNotification(Subscription::create([
                    'endpoint' => $s->endpoint,
                    'publicKey' => $s->p256dh,
                    'authToken' => $s->auth,
                ]), $payload);
            }
            foreach ($webPush->flush() as $report) {
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
