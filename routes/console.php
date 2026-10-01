<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Marketing Automation & Scheduled Delivery Engine
Schedule::command('marketing:dispatch-scheduled')->everyMinute();
Schedule::command('marketing:process-workflows')->everyMinute();
Schedule::command('marketing:evaluate-ab-tests')->hourly();
Schedule::command('marketing:decay-lead-scores')->daily();
Schedule::command('marketing:sunset-inactive')->daily();
