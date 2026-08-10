@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, ' . auth()->user()->name . '! Here\'s what\'s happening today.')

@section('content')
@php $u = auth()->user(); @endphp
{{-- Stats Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6 gap-5 mb-8">
    @if($u->hasPermissionGroup('reports'))
    <div class="admin-stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Revenue</p>
                <p class="text-2xl font-bold text-slate-900 mt-2">@money($stats['total_revenue'])</p>
            </div>
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-emerald-600 font-medium mt-3 flex items-center gap-1">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
            All time · paid orders only
        </p>
    </div>
    @endif

    @if($u->hasPermissionGroup('orders'))
    <div class="admin-stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Orders</p>
                <p class="text-2xl font-bold text-slate-900 mt-2">{{ number_format($stats['total_orders']) }}</p>
            </div>
            <div class="w-11 h-11 bg-indigo-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-indigo-600 font-medium mt-3">{{ $stats['pending_orders'] }} open · {{ $stats['status_pending_orders'] }} status pending</p>
    </div>
    @endif

    @if($u->hasPermissionGroup('products'))
    <div class="admin-stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Products</p>
                <p class="text-2xl font-bold text-slate-900 mt-2">{{ number_format($stats['total_products']) }}</p>
            </div>
            <div class="w-11 h-11 bg-violet-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-amber-600 font-medium mt-3">{{ $stats['low_stock'] }} low stock</p>
    </div>
    @endif

    @if($u->hasPermissionGroup('customers'))
    <div class="admin-stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Customers</p>
                <p class="text-2xl font-bold text-slate-900 mt-2">{{ number_format($stats['total_customers']) }}</p>
            </div>
            <div class="w-11 h-11 bg-sky-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-slate-500 font-medium mt-3">Registered users</p>
    </div>
    @endif

    @if($u->hasPermissionGroup('orders'))
    <div class="admin-stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Open Orders</p>
                <p class="text-2xl font-bold text-slate-900 mt-2">{{ number_format($stats['pending_orders']) }}</p>
            </div>
            <div class="w-11 h-11 bg-amber-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-amber-600 font-medium mt-3">{{ $stats['status_pending_orders'] }} awaiting confirmation</p>
    </div>
    @endif

    @if($u->hasPermissionGroup('masters'))
    <div class="admin-stat-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Categories</p>
                <p class="text-2xl font-bold text-slate-900 mt-2">{{ $topCategories->count() }}</p>
            </div>
            <div class="w-11 h-11 bg-rose-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-slate-500 font-medium mt-3">Active categories</p>
    </div>
    @endif
</div>

@if($u->hasPermissionGroup('reports') || $u->hasPermissionGroup('orders') || $u->hasPermissionGroup('masters'))
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    @if($u->hasPermissionGroup('reports'))
    {{-- Revenue Chart --}}
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Revenue Overview</h3>
                <p class="text-sm text-slate-500">Last 6 months performance</p>
            </div>
        </div>
        @php $maxRevenue = $monthlyRevenue->max() ?: 1; @endphp
        @if($monthlyRevenue->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p class="text-sm">No revenue data yet. Orders will appear here.</p>
            </div>
        @else
            <div class="flex items-end gap-3 h-48">
                @foreach($monthlyRevenue as $month => $revenue)
                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                        <p class="text-xs font-semibold text-slate-600">@money($revenue, 0)</p>
                        <div class="w-full bg-indigo-600 rounded-t-lg transition-all duration-500 hover:bg-indigo-500"
                            style="height: {{ max(12, ($revenue / $maxRevenue) * 160) }}px"></div>
                        <p class="text-xs text-slate-400 font-medium">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M') }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @endif

    @if($u->hasPermissionGroup('orders') || $u->hasPermissionGroup('masters'))
    {{-- Order Status --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        @if($u->hasPermissionGroup('orders'))
        <h3 class="text-lg font-bold text-slate-900 mb-1">Order Status</h3>
        <p class="text-sm text-slate-500 mb-6">Breakdown by status</p>
        @php
            $statusColors = [
                'pending' => ['bg' => 'bg-amber-500', 'light' => 'bg-amber-100', 'text' => 'text-amber-700'],
                'processing' => ['bg' => 'bg-indigo-500', 'light' => 'bg-indigo-100', 'text' => 'text-indigo-700'],
                'shipped' => ['bg' => 'bg-sky-500', 'light' => 'bg-sky-100', 'text' => 'text-sky-700'],
                'delivered' => ['bg' => 'bg-emerald-500', 'light' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
                'cancelled' => ['bg' => 'bg-red-500', 'light' => 'bg-red-100', 'text' => 'text-red-700'],
            ];
            $totalStatusOrders = $ordersByStatus->sum() ?: 1;
        @endphp
        @if($ordersByStatus->isEmpty())
            <div class="text-center py-10 text-slate-400 text-sm">No orders yet</div>
        @else
            <div class="space-y-4">
                @foreach($ordersByStatus as $status => $count)
                    @php $colors = $statusColors[$status] ?? ['bg' => 'bg-slate-500', 'light' => 'bg-slate-100', 'text' => 'text-slate-700']; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="font-medium text-slate-700 capitalize">{{ $status }}</span>
                            <span class="font-semibold {{ $colors['text'] }}">{{ $count }}</span>
                        </div>
                        <div class="h-2 {{ $colors['light'] }} rounded-full overflow-hidden">
                            <div class="h-full {{ $colors['bg'] }} rounded-full" style="width: {{ ($count / $totalStatusOrders) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        @endif

        @if($u->hasPermissionGroup('masters'))
        <div class="{{ $u->hasPermissionGroup('orders') ? 'mt-6 pt-6 border-t border-slate-100' : '' }}">
            <h4 class="text-sm font-semibold text-slate-700 mb-3">Top Categories</h4>
            <div class="space-y-2">
                @foreach($topCategories as $category)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">{{ $category->name }}</span>
                        <span class="font-medium text-slate-900 bg-slate-100 px-2 py-0.5 rounded-lg text-xs">{{ $category->products_count }} items</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
@endif

@if($u->hasPermissionGroup('orders'))
{{-- Recent Orders --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
        <div>
            <h3 class="text-lg font-bold text-slate-900">Recent Orders</h3>
            <p class="text-sm text-slate-500">Latest transactions from your store</p>
        </div>
    </div>

    @if($recentOrders->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-slate-400">
            <svg class="w-16 h-16 mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="font-medium text-slate-500">No orders yet</p>
            <p class="text-sm mt-1">Orders will appear here once customers start purchasing.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentOrders as $order)
                        @php
                            $badgeColors = [
                                'pending' => 'bg-amber-100 text-amber-700',
                                'processing' => 'bg-indigo-100 text-indigo-700',
                                'shipped' => 'bg-sky-100 text-sky-700',
                                'delivered' => 'bg-emerald-100 text-emerald-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-900 text-sm">{{ $order->order_number }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-semibold text-xs">
                                        {{ strtoupper(substr($order->user->name ?? 'G', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ $order->user->name ?? 'Guest' }}</p>
                                        <p class="text-xs text-slate-500">{{ $order->user->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full {{ $badgeColors[$order->status] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $order->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-900 text-sm">@money($order->displayTotal())</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endif

@if(! ($u->hasPermissionGroup('orders') || $u->hasPermissionGroup('products') || $u->hasPermissionGroup('customers') || $u->hasPermissionGroup('reports') || $u->hasPermissionGroup('masters')))
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-500">
    <p class="font-semibold text-slate-700">Welcome, {{ $u->name }}</p>
    <p class="text-sm mt-1">Use the menu on the left to manage your assigned sections.</p>
</div>
@endif
@endsection
