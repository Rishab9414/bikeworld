<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ShippingService
{
    public function __construct(
        private ManualShippingService $manual,
    ) {}

    public function defaultCharge(): float
    {
        return $this->manual->defaultProductShipping();
    }

    /**
     * @return array{success: bool, amount: float, source: string, note: ?string, breakdown?: array}
     */
    public function calculateForCart(Collection $items, ?string $destinationPin = null, string $paymentMethod = 'online', ?float $orderAmount = null): array
    {
        return $this->manual->calculateForCart($items, $orderAmount);
    }

    /**
     * @return array{has_details: bool, chargeable_weight_grams: int, actual_weight_grams: int, volumetric_weight_grams: int}
     */
    public function buildCartPackage(Collection $items): array
    {
        $hasDetails = false;
        $actualWeightGrams = 0;

        foreach ($items as $item) {
            $product = $item->product;
            $quantity = max(1, (int) $item->quantity);

            if (filled($product->weight) && (float) $product->weight > 0) {
                $hasDetails = true;
                $actualWeightGrams += (int) ceil((float) $product->weight * 1000) * $quantity;
            }
        }

        return [
            'has_details' => $hasDetails,
            'actual_weight_grams' => $actualWeightGrams,
            'volumetric_weight_grams' => 0,
            'chargeable_weight_grams' => max(100, $actualWeightGrams),
        ];
    }
}
