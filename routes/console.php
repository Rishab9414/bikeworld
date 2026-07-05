<?php

use App\Console\Commands\SyncStaleShipmentsCommand;
use App\Jobs\SyncTrackingJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Shipment tracking scheduler
|--------------------------------------------------------------------------
| Primary updates come from Delhivery webhooks. This job catches shipments
| that have not received a webhook/update within the stale window.
*/
if (config('jobs.shipment_sync_scheduler', true)) {
    Schedule::job(new SyncTrackingJob)
        ->everyThirtyMinutes()
        ->withoutOverlapping()
        ->onOneServer()
        ->name('sync-stale-shipments');
}

Schedule::command('queue:prune-failed', ['--hours' => 168])->daily();
