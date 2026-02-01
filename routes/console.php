<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled Jobs for Phase 10: Notifications
Schedule::job(new \App\Jobs\RetryFailedNotificationJob)->hourly();
Schedule::job(new \App\Jobs\CleanupNotificationLogsJob)->daily();
