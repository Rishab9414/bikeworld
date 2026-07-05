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

class SyncTrackingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(public ?int $staleMinutes = null)
    {
        $this->onQueue(config('jobs.queues.deliveries', 'deliveries'));
    }

    public function handle(DelhiveryService $delhivery): void
    {
        $staleMinutes = $this->staleMinutes ?? config('jobs.shipment_sync_stale_minutes', 30);
        $chunkSize = config('jobs.shipment_sync_chunk_size', 50);
        $terminal = ['delivered', 'cancelled', 'returned', 'rto', 'lost'];

        Shipment::query()
            ->whereNotIn('shipment_status', $terminal)
            ->where('updated_at', '<', now()->subMinutes($staleMinutes))
            ->whereNotNull('waybill')
            ->orderBy('updated_at')
            ->chunkById($chunkSize, function ($shipments) use ($delhivery) {
                foreach ($shipments as $shipment) {
                    try {
                        $delhivery->syncTracking($shipment);
                    } catch (\Throwable $e) {
                        Log::warning('SyncTrackingJob shipment failed', [
                            'shipment_id' => $shipment->id,
                            'waybill' => $shipment->waybill,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SyncTrackingJob batch failed', ['error' => $e->getMessage()]);
    }
}
