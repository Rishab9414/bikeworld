<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public int $orderId,
        public string $event,
        public ?string $message = null,
        public ?string $customSubject = null,
    ) {
        $this->onQueue(config('jobs.queues.notifications', 'notifications'));
    }

    public function handle(NotificationService $notifications): void
    {
        $order = Order::find($this->orderId);

        if (! $order) {
            return;
        }

        $notifications->sendOrderEvent($order, $this->event, $this->message, $this->customSubject);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendEmailJob failed', [
            'order_id' => $this->orderId,
            'event' => $this->event,
            'error' => $e->getMessage(),
        ]);
    }
}
