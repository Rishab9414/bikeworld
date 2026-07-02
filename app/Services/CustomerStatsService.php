<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;

class CustomerStatsService
{
    public function stats(Customer $customer): array
    {
        $orders = Order::query()
            ->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
                if ($customer->user_id) {
                    $q->orWhere('user_id', $customer->user_id);
                }
            })
            ->get();

        $totalOrders = $orders->count();
        $completed = $orders->where('status', 'delivered')->count();
        $cancelled = $orders->where('status', 'cancelled')->count();
        $pending = $orders->whereIn('status', ['pending', 'processing', 'shipped'])->count();
        $totalSpend = (float) $orders->where('status', '!=', 'cancelled')->sum('total');
        $avgOrder = $totalOrders > 0 ? $totalSpend / max(1, $totalOrders - $cancelled) : 0;

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completed,
            'cancelled_orders' => $cancelled,
            'pending_orders' => $pending,
            'total_spend' => $totalSpend,
            'average_order_value' => round($avgOrder, 2),
            'wishlist_count' => $customer->wishlists()->count(),
            'cart_items' => $customer->cartItems()->count(),
            'wallet_balance' => (float) ($customer->wallet?->current_balance ?? 0),
            'loyalty_points' => (int) ($customer->loyaltyPoint?->total_points ?? 0),
            'reviews_count' => $customer->reviews()->count(),
        ];
    }
}
