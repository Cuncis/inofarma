<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fase 6: return stock and mark an order kedaluwarsa once its 24-hour
// payment window lapses without payment. Five minutes is often enough that
// an order rarely sits expired-but-unprocessed for long, without hammering
// the database on a busy storefront.
Schedule::command('pesanan:kadaluwarsakan')->everyFiveMinutes();

// Fase 7.2: same idea for the 48-hour pickup window — an item staged at the
// counter that nobody collects goes back on the shelf automatically.
Schedule::command('pesanan:kadaluwarsakan-pengambilan')->everyFiveMinutes();
