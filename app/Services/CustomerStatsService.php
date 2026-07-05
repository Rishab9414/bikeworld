<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerStatsService
{
    /** Stats for a single customer (detail pages). */
    public function stats(Customer $customer): array
    {
        $orderStats = $this->listStats(collect([$customer]))[$customer->id] ?? [
            'total_orders' => 0,
            'total_spend' => 0.0,
        ];

        $orders = Order::query()
            ->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
                if ($customer->user_id) {
                    $q->orWhere('user_id', $customer->user_id);
                }
            })
            ->get(['id', 'status']);

        $totalOrders = $orderStats['total_orders'];
        $completed = $orders->where('status', 'delivered')->count();
        $cancelled = $orders->where('status', 'cancelled')->count();
        $pending = $orders->whereIn('status', ['pending', 'processing', 'shipped'])->count();
        $totalSpend = $orderStats['total_spend'];
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

    /**
     * Bulk order stats for admin customer list (2 SQL queries, no N+1).
     *
     * @return array<int, array{total_orders: int, total_spend: float}>
     */
    public function listStats(Collection $customers): array
    {
        if ($customers->isEmpty()) {
            return [];
        }

        $customerIds = $customers->pluck('id')->all();
        $userIdMap = $customers->filter(fn (Customer $c) => $c->user_id)
            ->pluck('id', 'user_id')
            ->all();
        $userIds = array_keys($userIdMap);

        $result = [];
        foreach ($customerIds as $id) {
            $result[$id] = ['total_orders' => 0, 'total_spend' => 0.0];
        }

        $spendExpr = $this->orderSpendSql();

        $byCustomer = Order::query()
            ->whereIn('customer_id', $customerIds)
            ->select('customer_id')
            ->selectRaw("COUNT(*) as cnt, SUM({$spendExpr}) as spend")
            ->groupBy('customer_id')
            ->get();

        foreach ($byCustomer as $row) {
            $result[(int) $row->customer_id]['total_orders'] += (int) $row->cnt;
            $result[(int) $row->customer_id]['total_spend'] += (float) $row->spend;
        }

        if ($userIds !== []) {
            $byUser = Order::query()
                ->whereIn('user_id', $userIds)
                ->where(function ($q) use ($customerIds) {
                    $q->whereNull('customer_id')
                        ->orWhereNotIn('customer_id', $customerIds);
                })
                ->select('user_id')
                ->selectRaw("COUNT(*) as cnt, SUM({$spendExpr}) as spend")
                ->groupBy('user_id')
                ->get();

            foreach ($byUser as $row) {
                $customerId = $userIdMap[(int) $row->user_id] ?? null;
                if ($customerId) {
                    $result[$customerId]['total_orders'] += (int) $row->cnt;
                    $result[$customerId]['total_spend'] += (float) $row->spend;
                }
            }
        }

        return $result;
    }

    private function orderSpendSql(): string
    {
        $cancelled = DB::getPdo()->quote('cancelled');

        return "CASE WHEN status != {$cancelled} THEN COALESCE(grand_total, total, 0) ELSE 0 END";
    }
}

