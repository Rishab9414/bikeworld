<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;

class ShippingService
{
    public function __construct(
        private DelhiveryService $delhivery,
    ) {}

    public function defaultCharge(): float
    {
        return (float) config('delhivery.default_shipping_charge', 99);
    }

    /**
     * @return array{success: bool, amount: float, source: string, note: ?string, chargeable_weight_grams?: int}
     */
    public function calculateForCart(Collection $items, ?string $destinationPin = null, string $paymentMethod = 'online', ?float $orderAmount = null): array
    {
        $default = $this->defaultCharge();

        if ($items->isEmpty()) {
            return $this->result($default, 'default', 'Cart is empty');
        }

        if ($orderAmount === null) {
            $orderAmount = (float) $items->sum(fn ($item) => $item->subtotal());
        }

        if (Setting::qualifiesForFreeShipping($orderAmount)) {
            return $this->result(
                0,
                'free_shipping_threshold',
                'Free shipping on orders above ₹'.number_format(Setting::freeShippingMinAmount(), 0)
            );
        }

        if ($items->every(fn ($item) => $item->product->free_shipping)) {
            return $this->result(0, 'free_shipping', 'Free shipping on all items');
        }

        $package = $this->buildCartPackage($items);

        if (! $package['has_details']) {
            return $this->result($default, 'default', 'Product shipping details not configured');
        }

        $pincode = preg_replace('/\D/', '', (string) $destinationPin);
        if (strlen($pincode) !== 6) {
            return $this->result($default, 'default', 'Enter pincode at checkout for live shipping quote');
        }

        $quote = $this->delhivery->calculateShippingCost(
            destinationPin: $pincode,
            weightGrams: $package['chargeable_weight_grams'],
            paymentMode: $paymentMethod === 'cod' ? 'COD' : 'Pre-paid',
        );

        if (! ($quote['success'] ?? false)) {
            return $this->result(
                $default,
                'default',
                $quote['message'] ?? 'Using default shipping charge',
                ['chargeable_weight_grams' => $package['chargeable_weight_grams']]
            );
        }

        return $this->result(
            (float) $quote['amount'],
            'delhivery',
            null,
            [
                'chargeable_weight_grams' => $package['chargeable_weight_grams'],
                'delhivery' => $quote,
            ]
        );
    }

    /**
     * @return array{has_details: bool, chargeable_weight_grams: int, actual_weight_grams: int, volumetric_weight_grams: int}
     */
    public function buildCartPackage(Collection $items): array
    {
        $hasDetails = false;
        $actualWeightGrams = 0;
        $volumetricWeightGrams = 0;

        foreach ($items as $item) {
            $product = $item->product;
            $quantity = max(1, (int) $item->quantity);

            $hasWeight = filled($product->weight) && (float) $product->weight > 0;
            $hasDimensions = filled($product->length) && filled($product->width) && filled($product->height);

            if ($hasWeight || $hasDimensions) {
                $hasDetails = true;
            }

            $weightKg = $hasWeight
                ? (float) $product->weight
                : (float) config('delhivery.default_weight_kg', 0.5);

            $actualWeightGrams += (int) ceil($weightKg * 1000) * $quantity;

            if ($hasDimensions) {
                $volumetricKg = ((float) $product->length * (float) $product->width * (float) $product->height) / 5000;
                $volumetricWeightGrams += (int) ceil($volumetricKg * 1000) * $quantity;
            }
        }

        return [
            'has_details' => $hasDetails,
            'actual_weight_grams' => $actualWeightGrams,
            'volumetric_weight_grams' => $volumetricWeightGrams,
            'chargeable_weight_grams' => max(100, $actualWeightGrams, $volumetricWeightGrams),
        ];
    }

    private function result(float $amount, string $source, ?string $note = null, array $extra = []): array
    {
        return array_merge([
            'success' => true,
            'amount' => round(max(0, $amount), 2),
            'source' => $source,
            'note' => $note,
        ], $extra);
    }
}
