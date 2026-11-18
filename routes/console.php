<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Search module: expire unanswered interests daily.
\Illuminate\Support\Facades\Schedule::command("interests:expire")->daily();
