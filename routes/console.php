<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Recurring task instances are generated 30 days ahead, nightly.
Schedule::command('tasks:generate-recurring --days=30')->dailyAt('02:00');
