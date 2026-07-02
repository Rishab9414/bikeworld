<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function checkAvailability(iterable $items): bool
    {
        foreach ($items as $item) {
            $product = $item->product ?? Product::find($item->product_id);
            if (! $product || $product->stock < $item->quantity) {
                return false;
            }
        }

        return true;
    }

    public function reserveStock(Order $order): void
    {
        if ($order->stock_reserved) {
            return;
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if (! $product || $product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for {$item->product_name}.",
                    ]);
                }
                $product->decrement('stock', $item->quantity);
                $item->update(['status' => 'reserved']);
            }
            $order->update(['stock_reserved' => true]);
        });
    }

    public function releaseStock(Order $order): void
    {
        if (! $order->stock_reserved) {
            return;
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                }
                $item->update(['status' => 'cancelled']);
            }
            $order->update(['stock_reserved' => false]);
        });
    }
}
