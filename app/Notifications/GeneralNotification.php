<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * One shared in-app notification used by every module.
 * type: like|comment|interest_received|interest_accepted|interest_rejected|
 *       account_approved|account_rejected|biodata_approved|biodata_rejected|
 *       new_biodata_area|news|event|system
 *
 * Usage: $user->notify(new GeneralNotification('like', 'Ramesh liked your photo', null, route('posts.show', $post)));
 */
class GeneralNotification extends Notification
{
    public function __construct(
        public string $type,
        public string $title,
        public ?string $body = null,
        public ?string $url = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => $this->type, 'title' => $this->title, 'body' => $this->body, 'url' => $this->url];
    }
}
