<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduler disabled
|--------------------------------------------------------------------------
| Background scheduled tasks have been removed. Shipment tracking and order
| statuses are updated manually from the admin order screen. Jobs still run
| synchronously (QUEUE_CONNECTION=sync), so no queue worker is required.
*/
