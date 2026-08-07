<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ManualShippingService;
use Illuminate\Console\Command;

class UpdateProductShippingCostsCommand extends Command
{
    protected $signature = 'products:update-shipping-costs {--dry-run : Preview changes without saving}';

    protected $description = 'Set product shipping_cost from weight: below 1kg = ₹99, 1–2kg = ₹150, above 2kg = ₹300';

    public function handle(ManualShippingService $shipping): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        Product::query()
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($shipping, $dryRun, &$updated) {
                foreach ($products as $product) {
                    $cost = $shipping->shippingCostForWeight(
                        filled($product->weight) ? (float) $product->weight : null
                    );

                    if ((float) $product->shipping_cost === $cost) {
                        continue;
                    }

                    $weightLabel = filled($product->weight) ? (float) $product->weight.' kg' : 'no weight';
                    $this->line(sprintf(
                        '#%d %s — weight: %s → shipping: ₹%s',
                        $product->id,
                        $product->name,
                        $weightLabel,
                        number_format($cost, 0)
                    ));

                    if (! $dryRun) {
                        $product->update(['shipping_cost' => $cost]);
                    }

                    $updated++;
                }
            });

        if ($updated === 0) {
            $this->info('No products needed updating.');

            return self::SUCCESS;
        }

        $this->info($dryRun
            ? "Dry run complete — {$updated} product(s) would be updated. Run without --dry-run to apply."
            : "Updated shipping cost on {$updated} product(s).");

        return self::SUCCESS;
    }
}
