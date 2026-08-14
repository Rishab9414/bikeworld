<?php

return [
    /*
    | Secret token for browser/cron URLs like /cron/sync-payments?token=...
    | Generate: php -r "echo bin2hex(random_bytes(16));"
    */
    'sync_token' => env('CRON_SYNC_TOKEN'),
];
