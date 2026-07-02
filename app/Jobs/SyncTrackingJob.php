<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Services\DelhiveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTrackingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?Shipment $shipment = null) {}

    public function handle(DelhiveryService $delhivery): void
    {
        if ($this->shipment) {
            $delhivery->syncTracking($this->shipment);

            return;
        }

        Shipment::whereNotIn('shipment_status', ['delivered', 'cancelled'])
            ->where('updated_at', '<', now()->subMinutes(30))
            ->each(fn (Shipment $s) => $delhivery->syncTracking($s));
    }
}
