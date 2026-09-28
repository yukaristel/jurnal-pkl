<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tutup absensi yang lupa pulang tengah malam
Schedule::command('absensi:close-pending')
    ->dailyAt('00:05')
    ->name('absensi-close-pending')
    ->withoutOverlapping();