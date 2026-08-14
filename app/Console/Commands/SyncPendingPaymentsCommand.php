<?php

namespace App\Console\Commands;

use App\Services\RazorpayService;
use Illuminate\Console\Command;

class SyncPendingPaymentsCommand extends Command
{
    protected $signature = 'orders:sync-payments {--limit=100 : Max orders to check} {--days=60 : Only orders from last N days}';

    protected $description = 'Check Razorpay API and mark paid for online orders still pending/failed';

    public function handle(RazorpayService $razorpay): int
    {
        if ($razorpay->usesMockMode()) {
            $this->warn('Razorpay is in mock mode or keys missing. Skipping.');

            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $days = (int) $this->option('days');

        $results = $razorpay->syncAllPendingPayments($limit, $days);

        $this->info("Checked: {$results['checked']}");
        $this->info('Updated to paid: '.count($results['updated']));
        $this->info('Still pending: '.count($results['still_pending']));
        $this->info('Errors: '.count($results['errors']));

        foreach ($results['updated'] as $row) {
            $this->line("  ✓ {$row['order_number']} → paid ({$row['razorpay_payment_id']})");
        }

        foreach ($results['errors'] as $row) {
            $this->error("  ✗ {$row['order_number']}: {$row['message']}");
        }

        return self::SUCCESS;
    }
}
