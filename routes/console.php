<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatic daily backup (DB + private files with signed contracts/evidence).
Schedule::command('app:backup')->dailyAt('03:00')->withoutOverlapping();

// Contract reminders (pending signature/review).
Schedule::command('contracts:reminders')->dailyAt('09:00')->withoutOverlapping();

// Monthly summary on the 1st of each month.
Schedule::command('contracts:monthly-summary')->monthlyOn(1, '08:00')->withoutOverlapping();

// GDPR Art. 5.1.e Data minimization: daily purge of orphaned temporary scans.
Schedule::command('contracts:purge-orphan-scans --hours=24')->dailyAt('04:00')->withoutOverlapping();
