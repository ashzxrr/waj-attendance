<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sync employees from HRIS every 30 minutes
Schedule::command('attendance:sync-employees')->everyThirtyMinutes();

// Sync unsynced attendance logs to HRIS every 15 minutes.
// Records with synced_to_hris_at still null remain eligible for retry
// on the next scheduled run if a previous sync attempt fails.
Schedule::command('attendance:sync-to-hris')->everyFifteenMinutes();
