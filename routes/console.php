<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler pengecekan LPJ overdue setiap hari pukul 00:01
Schedule::command('perjadin:check-overdue')->dailyAt('00:01');
