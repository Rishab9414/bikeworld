@extends('layouts.shop')
@section('title', 'My Account')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Welcome back, {{ $customer->first_name }}!</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $customer->customer_code }} · {{ ucfirst($customer->loyalty_tier) }} Member</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
        @foreach([
            ['label'=>'Total Orders','value'=>$stats['total_orders'],'icon'=>'📦'],
            ['label'=>'Pending','value'=>$stats['pending_orders'],'icon'=>'⏳'],
            ['label'=>'Delivered','value'=>$stats['completed_orders'],'icon'=>'✅'],
            ['label'=>'Wishlist','value'=>$stats['wishlist_count'],'icon'=>'❤️'],
            ['label'=>'Wallet','value'=>\App\Support\Money::format($stats['wallet_balance'], 0),'icon'=>'💰'],
            ['label'=>'Points','value'=>$stats['loyalty_points'],'icon'=>'⭐'],
        ] as $card)
        <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
            <p class="text-2xl mb-1">{{ $card['icon'] }}</p>
            <p class="text-xs text-slate-500 font-medium uppercase">{{ $card['label'] }}</p>
            <p class="text-xl font-bold text-slate-900 mt-1">{{ $card['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        {{-- Recent Orders --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-lg text-slate-900">Recent Orders</h2>
                <a href="{{ route('orders.index') }}" class="text-sm text-orange-600 font-semibold">View All →</a>
            </div>
            @forelse($recentOrders as $order)
            <div class="flex items-center justify-between py-3 border-b border-slate-50 last:border-0">
                <div>
                    <p class="font-semibold text-slate-900">{{ $order->order_number }}</p>
                    <p class="text-xs text-slate-500">{{ $order->created_at->format('M d, Y') }} · @money($order->displayTotal())</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-600">{{ $order->statusLabel() }}</span>
                    <a href="{{ route('orders.show', $order) }}" class="text-xs bg-orange-600 text-white px-3 py-1.5 rounded-lg font-semibold">Track</a>
                </div>
            </div>
            @empty
            <p class="text-slate-400 text-sm py-4">No orders yet. <a href="{{ route('products.index') }}" class="text-orange-600">Start shopping →</a></p>
            @endforelse
        </div>

        {{-- Quick Links --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h2 class="font-bold text-lg text-slate-900 mb-4">Quick Links</h2>
            <nav class="space-y-1">
                @foreach([
                    ['route'=>'account.profile','label'=>'Edit Profile','icon'=>'👤'],
                    ['route'=>'account.addresses','label'=>'Manage Addresses','icon'=>'📍'],
                    ['route'=>'orders.index','label'=>'My Orders','icon'=>'📦'],
                    ['route'=>'account.wishlist','label'=>'Wishlist','icon'=>'❤️'],
                    ['route'=>'account.wallet','label'=>'Wallet','icon'=>'💰'],
                    ['route'=>'account.loyalty','label'=>'Loyalty Points','icon'=>'⭐'],
                    ['route'=>'account.reviews','label'=>'My Reviews','icon'=>'💬'],
                    ['route'=>'cart.index','label'=>'My Cart','icon'=>'🛒'],
                ] as $link)
                <a href="{{ route($link['route']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                    <span>{{ $link['icon'] }}</span> {{ $link['label'] }}
                </a>
                @endforeach
            </nav>
        </div>
    </div>
</div>
@endsection
