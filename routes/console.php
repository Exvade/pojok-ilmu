<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (filled(config('services.haris.url')) && filled(config('services.haris.token'))) {
    Schedule::command('haris:sync-manpower')
        ->dailyAt((string) config('services.haris.sync_at'))
        ->withoutOverlapping();
}

Schedule::command('pojok-ilmu:generate-tna')
    ->dailyAt('02:30')
    ->withoutOverlapping();
