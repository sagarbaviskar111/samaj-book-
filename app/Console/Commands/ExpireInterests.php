<?php

namespace App\Console\Commands;

use App\Services\InterestService;
use Illuminate\Console\Command;

class ExpireInterests extends Command
{
    protected $signature = 'interests:expire';

    protected $description = 'Mark sent interests past their expiry date as expired';

    public function handle(InterestService $service): int
    {
        $this->info($service->expireDue().' interest(s) expired.');

        return self::SUCCESS;
    }
}
