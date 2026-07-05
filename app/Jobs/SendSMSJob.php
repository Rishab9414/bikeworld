<?php

namespace App\Jobs;

use App\Services\MessagingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public string $phone,
        public string $message,
        public ?string $template = null,
        public array $params = [],
    ) {
        $this->onQueue(config('jobs.queues.notifications', 'notifications'));
    }

    public function handle(MessagingService $messaging): void
    {
        $messaging->sendSms($this->phone, $this->message, $this->template, $this->params);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendSMSJob failed', ['phone' => $this->phone, 'error' => $e->getMessage()]);
    }
}
