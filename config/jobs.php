<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shipment tracking scheduler
    |--------------------------------------------------------------------------
    | Webhooks are primary; this catches shipments with no recent Delhivery update.
    */
    'shipment_sync_stale_minutes' => (int) env('SHIPMENT_SYNC_STALE_MINUTES', 30),
    'shipment_sync_scheduler' => env('SHIPMENT_SYNC_SCHEDULER', true),
    'shipment_sync_chunk_size' => (int) env('SHIPMENT_SYNC_CHUNK_SIZE', 50),

    'queues' => [
        'deliveries' => env('QUEUE_DELIVERIES', 'deliveries'),
        'notifications' => env('QUEUE_NOTIFICATIONS', 'notifications'),
        'default' => env('QUEUE_DEFAULT', 'default'),
    ],
];
