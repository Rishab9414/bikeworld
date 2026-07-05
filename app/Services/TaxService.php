<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class TaxService
{
    public const DEFAULT_RATE = 18.0;

    public function rateForProduct(Product $product): float
    {
        $product->loadMissing('tax');

        if ($product->tax) {
            return (float) $product->tax->percentage;
        }

        return self::DEFAULT_RATE;
    }

    public function lineBreakdown(Product $product, float $lineSubtotal): array
    {
        $rate = $this->rateForProduct($product) / 100;

        if ($this->isTaxIncluded($product)) {
            $tax = round($lineSubtotal - ($lineSubtotal / (1 + $rate)), 2);
            $exclusive = round($lineSubtotal - $tax, 2);

            return [
                'exclusive_amount' => $exclusive,
                'tax_amount' => $tax,
                'inclusive_amount' => round($lineSubtotal, 2),
                'tax_applied_at_checkout' => 0.0,
                'rate' => $this->rateForProduct($product),
                'tax_included' => true,
            ];
        }

        $tax = round($lineSubtotal * $rate, 2);

        return [
            'exclusive_amount' => round($lineSubtotal, 2),
            'tax_amount' => $tax,
            'inclusive_amount' => round($lineSubtotal + $tax, 2),
            'tax_applied_at_checkout' => $tax,
            'rate' => $this->rateForProduct($product),
            'tax_included' => false,
        ];
    }

    public function summarizeCart(Collection $cartItems): array
    {
        $exclusiveSubtotal = 0.0;
        $taxAmount = 0.0;
        $lines = [];

        foreach ($cartItems as $item) {
            $product = $item->product;
            $lineSubtotal = $item->subtotal();
            $breakdown = $this->lineBreakdown($product, $lineSubtotal);

            $exclusiveSubtotal += $breakdown['exclusive_amount'];
            $taxAmount += $breakdown['tax_amount'];

            $lines[] = array_merge($breakdown, [
                'cart_item_id' => $item->id,
                'product_id' => $product->id,
                'quantity' => $item->quantity,
                'line_subtotal' => $lineSubtotal,
            ]);
        }

        $exclusiveSubtotal = round($exclusiveSubtotal, 2);
        $taxAmount = round($taxAmount, 2);
        $checkoutTaxAmount = round(collect($lines)->sum('tax_applied_at_checkout'), 2);

        return [
            'subtotal' => $exclusiveSubtotal,
            'tax_amount' => $taxAmount,
            'checkout_tax_amount' => $checkoutTaxAmount,
            'items_total' => round($exclusiveSubtotal + $taxAmount, 2),
            'lines' => $lines,
            'has_inclusive_items' => collect($lines)->contains(fn ($l) => $l['tax_included']),
            'has_exclusive_items' => collect($lines)->contains(fn ($l) => ! $l['tax_included']),
        ];
    }

    public function taxLabel(Collection $cartItems): string
    {
        $rates = $cartItems
            ->map(fn ($item) => $this->rateForProduct($item->product))
            ->unique()
            ->sort()
            ->values();

        if ($rates->count() === 1) {
            return 'GST ('.rtrim(rtrim(number_format($rates->first(), 2), '0'), '.').'%)';
        }

        return 'GST';
    }

    public function isTaxIncluded(Product $product): bool
    {
        return (bool) $product->tax_included;
    }
}
