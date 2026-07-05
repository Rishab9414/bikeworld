<?php

namespace App\Console\Commands;

use App\Jobs\SyncTrackingJob;
use Illuminate\Console\Command;

class SyncStaleShipmentsCommand extends Command
{
    protected $signature = 'shipments:sync-stale {--minutes= : Stale threshold in minutes}';

    protected $description = 'Queue sync for in-transit shipments without recent Delhivery updates';

    public function handle(): int
    {
        $minutes = $this->option('minutes')
            ? (int) $this->option('minutes')
            : null;

        SyncTrackingJob::dispatch($minutes);

        $this->info('Stale shipment tracking sync queued.');

        return self::SUCCESS;
    }
}
