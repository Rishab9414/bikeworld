<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ActivityLogger;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $orderId,
        public ?int $actorUserId = null,
    ) {
        $this->onQueue(config('jobs.queues.default', 'default'));
    }

    public function handle(OrderService $orders): void
    {
        $order = Order::with('items')->find($this->orderId);

        if (! $order) {
            return;
        }

        $invoice = $orders->generateInvoice($order);

        ActivityLogger::log(
            'updated',
            'orders',
            $order,
            "Invoice generated: {$invoice->invoice_no}",
            userId: $this->actorUserId,
        );
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateInvoiceJob failed', ['order_id' => $this->orderId, 'error' => $e->getMessage()]);
    }
}
