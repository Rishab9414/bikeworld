<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $paidOrders = Order::query()->paid();
        $revenueExpression = 'COALESCE(grand_total, total, 0)';

        $stats = [
            'total_revenue' => (float) Order::query()
                ->revenueEligible()
                ->sum(DB::raw($revenueExpression)),
            'total_orders' => (clone $paidOrders)->count(),
            'total_products' => Product::count(),
            'total_customers' => User::where('is_admin', false)->count(),
            'pending_orders' => (clone $paidOrders)->awaitingFulfillment()->count(),
            'status_pending_orders' => (clone $paidOrders)->pendingOrders()->count(),
            'low_stock' => Product::where('stock', '<', 10)->count(),
        ];

        $recentOrders = Order::query()
            ->paid()
            ->with('user')
            ->latest()
            ->take(8)
            ->get();

        $ordersByStatus = Order::query()
            ->paid()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $monthlyRevenue = Order::query()
            ->revenueEligible()
            ->where('created_at', '>=', now()->subMonths(6))
            ->get(['grand_total', 'total', 'created_at'])
            ->groupBy(fn ($order) => $order->created_at->format('Y-m'))
            ->map(fn ($orders) => $orders->sum(fn (Order $order) => $order->displayTotal()))
            ->sortKeys();

        $topCategories = Category::withCount('products')
            ->orderByDesc('products_count')
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'stats',
            'recentOrders',
            'ordersByStatus',
            'monthlyRevenue',
            'topCategories'
        ));
    }
}
