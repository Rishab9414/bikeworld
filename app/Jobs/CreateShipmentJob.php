<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ActivityLogger;
use App\Services\DelhiveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateShipmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $orderId,
        public ?int $actorUserId = null,
    ) {
        $this->onQueue(config('jobs.queues.deliveries', 'deliveries'));
    }

    public function handle(DelhiveryService $delhivery): void
    {
        $order = Order::with(['items', 'customer'])->find($this->orderId);

        if (! $order || $order->shipment || $order->shipmentRecord) {
            return;
        }

        $shipment = $delhivery->createShipment($order);

        ActivityLogger::log(
            'updated',
            'orders',
            $order,
            "Shipment created for {$order->order_number} (AWB: {$shipment->waybill})",
            userId: $this->actorUserId,
        );

        GenerateLabelJob::dispatch($shipment->id, $this->actorUserId);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('CreateShipmentJob failed', ['order_id' => $this->orderId, 'error' => $e->getMessage()]);
    }
}
