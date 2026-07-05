<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Services\ActivityLogger;
use App\Services\DelhiveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateLabelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $shipmentId,
        public ?int $actorUserId = null,
    ) {
        $this->onQueue(config('jobs.queues.deliveries', 'deliveries'));
    }

    public function handle(DelhiveryService $delhivery): void
    {
        $shipment = Shipment::with('order')->find($this->shipmentId);

        if (! $shipment) {
            return;
        }

        $delhivery->generateLabel($shipment);

        if ($shipment->order) {
            ActivityLogger::log(
                'updated',
                'orders',
                $shipment->order,
                "Shipping label generated for AWB {$shipment->waybill}",
                userId: $this->actorUserId,
            );
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateLabelJob failed', ['shipment_id' => $this->shipmentId, 'error' => $e->getMessage()]);
    }
}
