<?php

namespace App\Console\Commands;

use App\Models\VehicleBrand;
use App\Services\BrandImageGenerator;
use Illuminate\Console\Command;

class GenerateVehicleBrandImages extends Command
{
    protected $signature = 'vehicle-brands:generate-images {--force : Regenerate even if image exists}';

    protected $description = 'Generate brand badge images for vehicle brands missing photos';

    public function handle(BrandImageGenerator $generator): int
    {
        $force = (bool) $this->option('force');
        $updated = 0;

        VehicleBrand::query()->orderBy('id')->each(function (VehicleBrand $brand) use ($generator, $force, &$updated) {
            if ($brand->image && ! $force) {
                $this->line("Skipped {$brand->name} (already has image)");

                return;
            }

            $path = $generator->generate($brand->name, $brand->slug);
            $brand->update(['image' => $path]);
            $updated++;
            $this->info("Generated image for {$brand->name}");
        });

        $this->info("Done. {$updated} brand image(s) created.");

        return self::SUCCESS;
    }
}
