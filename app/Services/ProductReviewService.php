<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Support\Collection;

class ProductReviewService
{
    /** @return Collection<int, OrderItem> */
    public function reviewableItemsForOrder(Order $order, Customer $customer): Collection
    {
        if (! in_array($order->status, ['delivered', 'completed'], true)) {
            return collect();
        }

        if ($order->customer_id !== $customer->id && $order->user_id !== $customer->user_id) {
            return collect();
        }

        $reviewedItemIds = ProductReview::query()
            ->where('customer_id', $customer->id)
            ->whereNotNull('order_item_id')
            ->pluck('order_item_id');

        return $order->items->reject(fn (OrderItem $item) => $reviewedItemIds->contains($item->id));
    }

    public function canReviewProduct(Product $product, Customer $customer): bool
    {
        return $this->findReviewableOrderItem($product, $customer) !== null;
    }

    public function findReviewableOrderItem(Product $product, Customer $customer): ?OrderItem
    {
        $existing = ProductReview::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($existing) {
            return null;
        }

        return OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', function ($q) use ($customer) {
                $q->whereIn('status', ['delivered', 'completed'])
                    ->where(function ($q2) use ($customer) {
                        $q2->where('customer_id', $customer->id);
                        if ($customer->user_id) {
                            $q2->orWhere('user_id', $customer->user_id);
                        }
                    });
            })
            ->latest('id')
            ->first();
    }

    public function store(Customer $customer, array $data): ProductReview
    {
        $product = Product::findOrFail($data['product_id']);
        $orderItem = null;

        if (! empty($data['order_item_id'])) {
            $orderItem = OrderItem::findOrFail($data['order_item_id']);
            abort_unless($orderItem->product_id === $product->id, 422);
        } else {
            $orderItem = $this->findReviewableOrderItem($product, $customer);
        }

        abort_unless($orderItem !== null, 403, 'You can only review products from delivered orders.');

        return ProductReview::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'order_item_id' => $orderItem->id,
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'review' => $data['review'],
            'verified_purchase' => true,
            'status' => 'approved',
        ]);
    }

    /** @return array{average: float, count: int} */
    public function summaryForProduct(Product $product): array
    {
        $stats = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('status', 'approved')
            ->selectRaw('AVG(rating) as average, COUNT(*) as count')
            ->first();

        return [
            'average' => round((float) ($stats->average ?? 0), 1),
            'count' => (int) ($stats->count ?? 0),
        ];
    }
}
