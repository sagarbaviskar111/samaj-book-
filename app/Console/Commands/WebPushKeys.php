<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class WebPushKeys extends Command
{
    protected $signature = 'webpush:keys';

    protected $description = 'Generate VAPID keys for Web Push and print the .env lines';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();
        $this->line('Add these to your .env file:');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY='.$keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY='.$keys['privateKey']);
        $this->line('VAPID_SUBJECT=mailto:admin@example.com');

        return self::SUCCESS;
    }
}
