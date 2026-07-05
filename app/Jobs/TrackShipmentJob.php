<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Services\DelhiveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TrackShipmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public int $shipmentId)
    {
        $this->onQueue(config('jobs.queues.deliveries', 'deliveries'));
    }

    public function handle(DelhiveryService $delhivery): void
    {
        $shipment = Shipment::find($this->shipmentId);

        if (! $shipment) {
            return;
        }

        $delhivery->syncTracking($shipment);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('TrackShipmentJob failed', ['shipment_id' => $this->shipmentId, 'error' => $e->getMessage()]);
    }
}
