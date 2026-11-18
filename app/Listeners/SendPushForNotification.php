<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\PushService;
use Illuminate\Notifications\Events\NotificationSent;

class SendPushForNotification
{
    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'database' || ! $event->notifiable instanceof User) {
            return;
        }
        if (! $event->notification instanceof GeneralNotification) {
            return;
        }
        $n = $event->notification;
        PushService::toUser($event->notifiable, $n->title, (string) $n->body, $n->url, $n->type);
    }
}
