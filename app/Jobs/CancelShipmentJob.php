<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\ActivityLogger;
use App\Services\DelhiveryService;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CancelShipmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $shipmentId,
        public int $orderId,
        public ?int $actorUserId = null,
    ) {
        $this->onQueue(config('jobs.queues.deliveries', 'deliveries'));
    }

    public function handle(DelhiveryService $delhivery, OrderService $orders): void
    {
        $shipment = Shipment::find($this->shipmentId);
        $order = Order::find($this->orderId);

        if (! $shipment || ! $order) {
            return;
        }

        $delhivery->cancelShipment($shipment);
        $orders->updateStatus($order, 'cancelled', 'Shipment Cancelled', null, 'admin');

        ActivityLogger::log(
            'updated',
            'orders',
            $order,
            "Shipment cancelled for {$order->order_number}",
            userId: $this->actorUserId,
        );
    }

    public function failed(\Throwable $e): void
    {
        Log::error('CancelShipmentJob failed', [
            'shipment_id' => $this->shipmentId,
            'order_id' => $this->orderId,
            'error' => $e->getMessage(),
        ]);
    }
}
