<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function items(): Collection
    {
        return $this->query()->with(['product.category', 'product.tax', 'variant'])->get();
    }

    public function count(): int
    {
        return (int) $this->query()->sum('quantity');
    }

    public function subtotal(): float
    {
        return $this->items()->sum(fn (CartItem $item) => $item->subtotal());
    }

    public function add(Product $product, int $quantity = 1, ?ProductVariant $variant = null): void
    {
        $availableStock = $variant?->stock ?? $product->stock;
        $query = $this->query()->where('product_id', $product->id);

        if ($variant) {
            $query->where('variant_id', $variant->id);
        } else {
            $query->whereNull('variant_id');
        }

        $item = $query->first();

        if ($item) {
            $item->update([
                'quantity' => min($item->quantity + $quantity, $availableStock),
            ]);

            return;
        }

        CartItem::create([
            'user_id' => Auth::id(),
            'session_id' => Auth::check() ? null : session()->getId(),
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => min($quantity, $availableStock),
        ]);
    }

    public function update(CartItem $item, int $quantity): void
    {
        if ($quantity <= 0) {
            $item->delete();

            return;
        }

        $item->update([
            'quantity' => min($quantity, $item->variant?->stock ?? $item->product->stock),
        ]);
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(): void
    {
        $this->query()->delete();
    }

    public function mergeGuestCart(int $userId): void
    {
        $sessionId = session()->getId();

        CartItem::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->each(function (CartItem $guestItem) use ($userId) {
                $existing = CartItem::where('user_id', $userId)
                    ->where('product_id', $guestItem->product_id)
                    ->when(
                        $guestItem->variant_id,
                        fn ($query) => $query->where('variant_id', $guestItem->variant_id),
                        fn ($query) => $query->whereNull('variant_id'),
                    )
                    ->first();

                if ($existing) {
                    $existing->update([
                        'quantity' => min(
                            $existing->quantity + $guestItem->quantity,
                            $guestItem->product->stock
                        ),
                    ]);
                    $guestItem->delete();
                } else {
                    $guestItem->update([
                        'user_id' => $userId,
                        'session_id' => null,
                    ]);
                }
            });
    }

    private function query()
    {
        if (Auth::check()) {
            return CartItem::where('user_id', Auth::id());
        }

        return CartItem::where('session_id', session()->getId())->whereNull('user_id');
    }
}
