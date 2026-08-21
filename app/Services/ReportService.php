<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /** @var list<string> */
    public const REVENUE_EXCLUDED_STATUSES = ['cancelled', 'refunded'];

    /** @var array<string, array{title: string, description: string, icon: string}> */
    public const REPORT_TYPES = [
        'sales' => [
            'title' => 'Sales & Revenue',
            'description' => 'Revenue, orders, discounts, tax and shipping breakdown',
            'icon' => 'chart',
        ],
        'orders' => [
            'title' => 'Orders',
            'description' => 'Order volume, status funnel and detailed order list',
            'icon' => 'orders',
        ],
        'products' => [
            'title' => 'Products',
            'description' => 'Best sellers, category performance and product revenue',
            'icon' => 'products',
        ],
        'customers' => [
            'title' => 'Customers',
            'description' => 'Registrations, top spenders and repeat buyers',
            'icon' => 'customers',
        ],
        'coupons' => [
            'title' => 'Coupons',
            'description' => 'Coupon usage, discounts given and attributed revenue',
            'icon' => 'coupons',
        ],
        'payments' => [
            'title' => 'Payments',
            'description' => 'Payment methods, status breakdown and Razorpay collections',
            'icon' => 'payments',
        ],
        'inventory' => [
            'title' => 'Inventory',
            'description' => 'Stock levels, low stock alerts and inventory value',
            'icon' => 'inventory',
        ],
        'shipping' => [
            'title' => 'Shipping',
            'description' => 'Shipments by status, courier costs and delivery performance',
            'icon' => 'shipping',
        ],
    ];

    /** @return array{from: ?Carbon, to: ?Carbon, preset: string} */
    public function parseDateRange(Request $request): array
    {
        $preset = $request->input('preset', '30d');

        return match ($preset) {
            'today' => [
                'from' => now()->startOfDay(),
                'to' => now()->endOfDay(),
                'preset' => 'today',
            ],
            '7d' => [
                'from' => now()->subDays(6)->startOfDay(),
                'to' => now()->endOfDay(),
                'preset' => '7d',
            ],
            '30d' => [
                'from' => now()->subDays(29)->startOfDay(),
                'to' => now()->endOfDay(),
                'preset' => '30d',
            ],
            'month' => [
                'from' => now()->startOfMonth(),
                'to' => now()->endOfDay(),
                'preset' => 'month',
            ],
            'year' => [
                'from' => now()->startOfYear(),
                'to' => now()->endOfDay(),
                'preset' => 'year',
            ],
            'all' => [
                'from' => null,
                'to' => null,
                'preset' => 'all',
            ],
            'custom' => [
                'from' => $request->filled('date_from')
                    ? Carbon::parse($request->date_from)->startOfDay()
                    : now()->subDays(29)->startOfDay(),
                'to' => $request->filled('date_to')
                    ? Carbon::parse($request->date_to)->endOfDay()
                    : now()->endOfDay(),
                'preset' => 'custom',
            ],
            default => [
                'from' => now()->subDays(29)->startOfDay(),
                'to' => now()->endOfDay(),
                'preset' => '30d',
            ],
        };
    }

    public function salesReport(?Carbon $from, ?Carbon $to): array
    {
        $orders = $this->ordersInRange($from, $to)->get();
        $revenueOrders = $orders->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES);

        $revenue = $this->sumOrderTotals($revenueOrders);
        $orderCount = $orders->count();
        $paidCount = $orderCount;

        $daily = $revenueOrders
            ->groupBy(fn (Order $o) => $o->created_at->format('Y-m-d'))
            ->map(fn (Collection $group) => [
                'date' => $group->first()->created_at->format('Y-m-d'),
                'orders' => $group->count(),
                'revenue' => $this->sumOrderTotals($group),
            ])
            ->sortKeys()
            ->values();

        $byPaymentMethod = $revenueOrders
            ->groupBy(fn (Order $o) => $o->payment_method ?: 'unknown')
            ->map(fn (Collection $group, string $method) => [
                'method' => $method,
                'label' => $this->paymentMethodLabel($method),
                'orders' => $group->count(),
                'revenue' => $this->sumOrderTotals($group),
            ])
            ->sortByDesc('revenue')
            ->values();

        return [
            'summary' => [
                'revenue' => $revenue,
                'orders' => $orderCount,
                'paid_orders' => $paidCount,
                'average_order_value' => $revenueOrders->count() > 0
                    ? round($revenue / $revenueOrders->count(), 2)
                    : 0,
                'subtotal' => (float) $revenueOrders->sum('subtotal'),
                'discount' => (float) $revenueOrders->sum('discount'),
                'coupon_discount' => (float) $revenueOrders->sum('coupon_discount'),
                'wallet_discount' => (float) $revenueOrders->sum('wallet_discount'),
                'shipping_collected' => (float) $revenueOrders->sum('shipping_charge'),
                'tax_collected' => (float) $revenueOrders->sum('tax_amount'),
                'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
                'refunded_orders' => $orders->whereIn('status', ['refunded', 'returned'])->count(),
            ],
            'daily' => $daily,
            'by_payment_method' => $byPaymentMethod,
        ];
    }

    public function ordersReport(?Carbon $from, ?Carbon $to): array
    {
        $orders = $this->ordersInRange($from, $to)
            ->with(['user:id,name,email', 'customer:id,full_name,email'])
            ->withCount('items')
            ->latest()
            ->get();

        $byStatus = $orders
            ->groupBy('status')
            ->map(fn (Collection $group, string $status) => [
                'status' => $status,
                'label' => ucwords(str_replace('_', ' ', $status)),
                'count' => $group->count(),
                'revenue' => $this->sumOrderTotals($group->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)),
            ])
            ->sortByDesc('count')
            ->values();

        $rows = $orders->map(fn (Order $o) => [
            'order_number' => $o->order_number,
            'date' => $o->created_at->format('Y-m-d H:i'),
            'customer' => $o->customer?->full_name ?? $o->user?->name ?? 'Guest',
            'email' => $o->customer?->email ?? $o->user?->email ?? '',
            'status' => $o->statusLabel(),
            'payment_method' => $this->paymentMethodLabel($o->payment_method ?? ''),
            'payment_status' => ucfirst($o->payment_status ?? 'pending'),
            'items' => $o->items_count,
            'total' => $o->displayTotal(),
        ]);

        return [
            'summary' => [
                'total' => $orders->count(),
                'delivered' => $orders->where('status', 'delivered')->count(),
                'in_progress' => $orders->whereNotIn('status', ['delivered', 'completed', 'cancelled', 'refunded', 'returned'])->count(),
                'cancelled' => $orders->where('status', 'cancelled')->count(),
                'revenue' => $this->sumOrderTotals($orders->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)),
            ],
            'by_status' => $byStatus,
            'rows' => $rows,
        ];
    }

    public function productsReport(?Carbon $from, ?Carbon $to): array
    {
        $itemsQuery = OrderItem::query()
            ->whereHas('order', function (Builder $q) use ($from, $to) {
                $this->applyPaidOrderScope($q);
                $this->applyDateRange($q, $from, $to);
                $q->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES);
            })
            ->with('product.category:id,name');

        $items = $itemsQuery->get();

        $bestSellers = $items
            ->groupBy('product_id')
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'product_id' => $first->product_id,
                    'name' => $first->product_name,
                    'sku' => $first->sku,
                    'category' => $first->product?->category?->name ?? '—',
                    'quantity_sold' => (int) $group->sum('quantity'),
                    'revenue' => (float) $group->sum(fn (OrderItem $i) => $i->lineTotal()),
                    'orders' => $group->pluck('order_id')->unique()->count(),
                ];
            })
            ->sortByDesc('quantity_sold')
            ->take(25)
            ->values();

        $byCategory = $items
            ->groupBy(fn (OrderItem $i) => $i->product?->category_id ?? 0)
            ->map(function (Collection $group) {
                $categoryName = $group->first()->product?->category?->name ?? 'Uncategorized';

                return [
                    'category' => $categoryName,
                    'quantity_sold' => (int) $group->sum('quantity'),
                    'revenue' => (float) $group->sum(fn (OrderItem $i) => $i->lineTotal()),
                    'products' => $group->pluck('product_id')->unique()->count(),
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $outOfStock = Product::where('stock', '<=', 0)->count();

        return [
            'summary' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'out_of_stock' => $outOfStock,
                'units_sold' => (int) $items->sum('quantity'),
                'product_revenue' => (float) $items->sum(fn (OrderItem $i) => $i->lineTotal()),
            ],
            'best_sellers' => $bestSellers,
            'by_category' => $byCategory,
        ];
    }

    public function customersReport(?Carbon $from, ?Carbon $to): array
    {
        $customersQuery = Customer::query();
        if ($from) {
            $customersQuery->where('created_at', '>=', $from);
        }
        if ($to) {
            $customersQuery->where('created_at', '<=', $to);
        }

        $newCustomers = (clone $customersQuery)->count();

        $orderStats = DB::table('orders')
            ->select('customer_id', 'user_id')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(COALESCE(grand_total, total, 0)) as total_spend')
            ->where('payment_status', 'paid')
            ->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->groupBy('customer_id', 'user_id')
            ->get();

        $topCustomers = Customer::query()
            ->withCount(['orders' => function (Builder $q) use ($from, $to) {
                $this->applyPaidOrderScope($q);
                $this->applyDateRange($q, $from, $to);
            }])
            ->get()
            ->map(function (Customer $c) use ($from, $to) {
                $spend = (float) Order::query()
                    ->paid()
                    ->where(function (Builder $q) use ($c) {
                        $q->where('customer_id', $c->id);
                        if ($c->user_id) {
                            $q->orWhere('user_id', $c->user_id);
                        }
                    })
                    ->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)
                    ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
                    ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
                    ->get()
                    ->sum(fn (Order $o) => $o->displayTotal());

                return [
                    'code' => $c->customer_code,
                    'name' => $c->full_name,
                    'email' => $c->email,
                    'mobile' => $c->mobile,
                    'registered' => $c->created_at?->format('Y-m-d'),
                    'orders' => $c->orders_count,
                    'total_spend' => $spend,
                ];
            })
            ->filter(fn (array $row) => $row['total_spend'] > 0 || $row['orders'] > 0)
            ->sortByDesc('total_spend')
            ->take(25)
            ->values();

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('account_status', 'active')->count();

        $repeatBuyers = $orderStats->where('order_count', '>', 1)->count();
        $oneTimeBuyers = $orderStats->where('order_count', 1)->count();

        $registrationsByDay = Customer::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->get()
            ->groupBy(fn (Customer $c) => $c->created_at->format('Y-m-d'))
            ->map(fn (Collection $group, string $date) => [
                'date' => $date,
                'count' => $group->count(),
            ])
            ->sortKeys()
            ->values();

        return [
            'summary' => [
                'total_customers' => $totalCustomers,
                'active_customers' => $activeCustomers,
                'new_customers' => $newCustomers,
                'repeat_buyers' => $repeatBuyers,
                'one_time_buyers' => $oneTimeBuyers,
            ],
            'top_customers' => $topCustomers,
            'registrations_by_day' => $registrationsByDay,
        ];
    }

    public function couponsReport(?Carbon $from, ?Carbon $to): array
    {
        $usagesQuery = CouponUsage::query()
            ->with(['coupon:id,code,type,value', 'order:id,order_number,grand_total,total,status,payment_status,created_at'])
            ->whereHas('order', fn (Builder $q) => $q->paid())
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to));

        $usages = $usagesQuery->get();

        $byCoupon = $usages
            ->groupBy('coupon_id')
            ->map(function (Collection $group) {
                $coupon = $group->first()->coupon;

                return [
                    'code' => $coupon?->code ?? '—',
                    'type' => $coupon?->typeLabel() ?? '—',
                    'value' => $coupon?->valueLabel() ?? '—',
                    'usage_count' => $group->count(),
                    'discount_given' => (float) $group->sum('discount_amount'),
                    'order_revenue' => (float) $group
                        ->filter(fn (CouponUsage $u) => $u->order && ! in_array($u->order->status, self::REVENUE_EXCLUDED_STATUSES, true))
                        ->sum(fn (CouponUsage $u) => $u->order->displayTotal()),
                ];
            })
            ->sortByDesc('usage_count')
            ->values();

        $ordersWithCoupon = Order::query()
            ->paid()
            ->whereNotNull('coupon_id')
            ->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->get();

        return [
            'summary' => [
                'total_coupons' => Coupon::count(),
                'active_coupons' => Coupon::where('is_active', true)->count(),
                'total_usages' => $usages->count(),
                'total_discount' => (float) $usages->sum('discount_amount'),
                'orders_with_coupon' => $ordersWithCoupon->count(),
                'coupon_order_revenue' => $this->sumOrderTotals($ordersWithCoupon),
            ],
            'by_coupon' => $byCoupon,
        ];
    }

    public function paymentsReport(?Carbon $from, ?Carbon $to): array
    {
        $orders = $this->ordersInRange($from, $to)->get();

        $byStatus = $orders
            ->groupBy(fn (Order $o) => $o->payment_status ?: 'pending')
            ->map(fn (Collection $group, string $status) => [
                'status' => $status,
                'label' => ucfirst($status),
                'count' => $group->count(),
                'amount' => $this->sumOrderTotals($group->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)),
            ])
            ->sortByDesc('count')
            ->values();

        $byMethod = $orders
            ->groupBy(fn (Order $o) => $o->payment_method ?: 'unknown')
            ->map(fn (Collection $group, string $method) => [
                'method' => $method,
                'label' => $this->paymentMethodLabel($method),
                'count' => $group->count(),
                'paid' => $group->count(),
                'pending' => 0,
                'amount' => $this->sumOrderTotals($group->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)),
            ])
            ->sortByDesc('amount')
            ->values();

        $razorpayOrders = $orders->filter(fn (Order $o) => in_array($o->payment_method, ['razorpay', 'online'], true));
        $codOrders = $orders->filter(fn (Order $o) => in_array($o->payment_method, ['cod', 'cash_on_delivery'], true));

        $rows = $orders
            ->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)
            ->sortByDesc('created_at')
            ->take(100)
            ->map(fn (Order $o) => [
                'order_number' => $o->order_number,
                'date' => $o->created_at->format('Y-m-d H:i'),
                'method' => $this->paymentMethodLabel($o->payment_method ?? ''),
                'status' => ucfirst($o->payment_status ?? 'pending'),
                'razorpay_id' => $o->razorpay_payment_id ?? '—',
                'paid_at' => $o->paid_at?->format('Y-m-d H:i') ?? '—',
                'amount' => $o->displayTotal(),
            ])
            ->values();

        return [
            'summary' => [
                'total_collected' => $this->sumOrderTotals($orders->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)),
                'pending_collection' => 0,
                'razorpay_orders' => $razorpayOrders->count(),
                'razorpay_collected' => $this->sumOrderTotals($razorpayOrders),
                'cod_orders' => $codOrders->count(),
                'cod_pending' => 0,
            ],
            'by_status' => $byStatus,
            'by_method' => $byMethod,
            'rows' => $rows,
        ];
    }

    public function inventoryReport(): array
    {
        $products = Product::with(['category:id,name', 'brand:id,name'])
            ->orderBy('stock')
            ->get();

        $lowStock = $products->filter(function (Product $p) {
            $threshold = $p->low_stock_alert ?: $p->reorder_level ?: 10;

            return $p->stock > 0 && $p->stock <= $threshold;
        });

        $outOfStock = $products->where('stock', '<=', 0);
        $inStock = $products->where('stock', '>', 0);

        $inventoryValue = $products->sum(fn (Product $p) => $p->stock * (float) ($p->purchase_price ?: $p->landing_cost ?: $p->selling_price ?: $p->price));
        $retailValue = $products->sum(fn (Product $p) => $p->stock * (float) ($p->selling_price ?: $p->price));

        $rows = $products->map(fn (Product $p) => [
            'sku' => $p->sku,
            'name' => $p->name,
            'category' => $p->category?->name ?? '—',
            'brand' => $p->brand?->name ?? '—',
            'stock' => (int) $p->stock,
            'reserved' => (int) ($p->reserved_stock ?? 0),
            'available' => max(0, (int) $p->stock - (int) ($p->reserved_stock ?? 0)),
            'alert_level' => (int) ($p->low_stock_alert ?: $p->reorder_level ?: 10),
            'cost_value' => round($p->stock * (float) ($p->purchase_price ?: $p->landing_cost ?: 0), 2),
            'retail_value' => round($p->stock * (float) ($p->selling_price ?: $p->price), 2),
            'status' => $p->stock <= 0 ? 'Out of stock' : ($p->stock <= ($p->low_stock_alert ?: 10) ? 'Low stock' : 'In stock'),
        ]);

        $byCategory = $products
            ->groupBy('category_id')
            ->map(function (Collection $group) {
                return [
                    'category' => $group->first()->category?->name ?? 'Uncategorized',
                    'products' => $group->count(),
                    'total_stock' => (int) $group->sum('stock'),
                    'out_of_stock' => $group->where('stock', '<=', 0)->count(),
                    'low_stock' => $group->filter(fn (Product $p) => $p->stock > 0 && $p->stock <= ($p->low_stock_alert ?: 10))->count(),
                ];
            })
            ->sortByDesc('total_stock')
            ->values();

        return [
            'summary' => [
                'total_skus' => $products->count(),
                'in_stock' => $inStock->count(),
                'low_stock' => $lowStock->count(),
                'out_of_stock' => $outOfStock->count(),
                'total_units' => (int) $products->sum('stock'),
                'inventory_cost_value' => round($inventoryValue, 2),
                'inventory_retail_value' => round($retailValue, 2),
            ],
            'by_category' => $byCategory,
            'rows' => $rows,
        ];
    }

    public function shippingReport(?Carbon $from, ?Carbon $to): array
    {
        $shipmentsQuery = Shipment::query()->with(['order:id,order_number,status,grand_total,total,shipping_charge,created_at']);

        if ($from || $to) {
            $shipmentsQuery->whereHas('order', function (Builder $q) use ($from, $to) {
                $this->applyPaidOrderScope($q);
                $this->applyDateRange($q, $from, $to);
            });
        } else {
            $shipmentsQuery->whereHas('order', fn (Builder $q) => $this->applyPaidOrderScope($q));
        }

        $shipments = $shipmentsQuery->latest()->get();

        $byStatus = $shipments
            ->groupBy(fn (Shipment $s) => $s->shipment_status ?: 'unknown')
            ->map(fn (Collection $group, string $status) => [
                'status' => $status,
                'label' => ucwords(str_replace('_', ' ', $status)),
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values();

        $byCourier = $shipments
            ->groupBy(fn (Shipment $s) => $s->courier_name ?: 'Unknown')
            ->map(fn (Collection $group, string $courier) => [
                'courier' => $courier,
                'count' => $group->count(),
                'shipping_cost' => (float) $group->sum('shipping_cost'),
            ])
            ->sortByDesc('count')
            ->values();

        $delivered = $shipments->filter(fn (Shipment $s) => in_array($s->shipment_status, ['delivered', 'Delivered'], true));
        $inTransit = $shipments->filter(fn (Shipment $s) => ! in_array($s->shipment_status, ['delivered', 'Delivered', 'cancelled', 'Cancelled', 'RTO', 'rto'], true));

        $rows = $shipments->map(fn (Shipment $s) => [
            'order_number' => $s->order?->order_number ?? '—',
            'waybill' => $s->waybill ?? $s->tracking_number ?? '—',
            'courier' => $s->courier_name ?? '—',
            'status' => $s->statusLabel(),
            'shipping_cost' => (float) ($s->shipping_cost ?? 0),
            'charged' => (float) ($s->order?->shipping_charge ?? 0),
            'pickup_date' => $s->pickup_date?->format('Y-m-d') ?? '—',
            'estimated_delivery' => $s->estimated_delivery?->format('Y-m-d') ?? '—',
            'created' => $s->created_at?->format('Y-m-d') ?? '—',
        ]);

        $shippingCharged = (float) $shipments->sum(fn (Shipment $s) => (float) ($s->order?->shipping_charge ?? 0));
        $shippingCost = (float) $shipments->sum('shipping_cost');

        return [
            'summary' => [
                'total_shipments' => $shipments->count(),
                'delivered' => $delivered->count(),
                'in_transit' => $inTransit->count(),
                'shipping_charged' => $shippingCharged,
                'shipping_cost' => $shippingCost,
                'margin' => round($shippingCharged - $shippingCost, 2),
            ],
            'by_status' => $byStatus,
            'by_courier' => $byCourier,
            'rows' => $rows,
        ];
    }

    public function overviewStats(?Carbon $from, ?Carbon $to): array
    {
        $sales = $this->salesReport($from, $to);

        return [
            'revenue' => $sales['summary']['revenue'],
            'orders' => $sales['summary']['orders'],
            'average_order_value' => $sales['summary']['average_order_value'],
            'new_customers' => Customer::query()
                ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
                ->count(),
            'products_sold' => (int) OrderItem::query()
                ->whereHas('order', function (Builder $q) use ($from, $to) {
                    $this->applyPaidOrderScope($q);
                    $this->applyDateRange($q, $from, $to);
                    $q->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES);
                })
                ->sum('quantity'),
        ];
    }

    private function ordersInRange(?Carbon $from, ?Carbon $to): Builder
    {
        $query = Order::query()->paid();
        $this->applyDateRange($query, $from, $to);

        return $query;
    }

    private function applyPaidOrderScope(Builder $query): Builder
    {
        return $query->paid();
    }

    private function applyDateRange(Builder $query, ?Carbon $from, ?Carbon $to): void
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
    }

    /** @param Collection<int, Order>|iterable<Order> $orders */
    private function sumOrderTotals(iterable $orders): float
    {
        return (float) collect($orders)->sum(fn (Order $o) => $o->displayTotal());
    }

    private function paymentMethodLabel(string $method): string
    {
        return match (strtolower($method)) {
            'razorpay', 'online' => 'Online (Razorpay)',
            'cod', 'cash_on_delivery' => 'Cash on Delivery',
            'wallet' => 'Wallet',
            '', 'unknown' => 'Not specified',
            default => ucwords(str_replace('_', ' ', $method)),
        };
    }
}
