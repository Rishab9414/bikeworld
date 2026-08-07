@extends('layouts.shop')

@section('title', 'Order ' . $order->order_number . ' - ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('orders.index') }}" class="text-brand-red hover:text-red-700 text-sm font-semibold">&larr; Back to Orders</a>
        <h1 class="text-3xl font-black text-brand-black mt-2">Order {{ $order->order_number }}</h1>
        <p class="text-zinc-500 mt-1">Placed on {{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
    </div>

    @if($order->payment_status === 'pending' && $order->payment_method === 'online')
    <div class="bg-amber-50 border border-amber-200 text-amber-900 px-5 py-4 rounded-2xl mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="font-bold">Payment Pending</p>
            <p class="text-sm mt-1">Complete payment to confirm your order.</p>
        </div>
        <a href="{{ route('orders.payment', $order) }}" class="inline-flex justify-center bg-brand-red text-white font-bold px-6 py-3 rounded-xl hover:bg-red-700 shrink-0">
            Pay @money($order->displayTotal()) Now
        </a>
    </div>
    @endif

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 text-emerald-800 rounded-xl text-sm border border-emerald-100">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-zinc-100 p-6 mb-6 shadow-sm">
        <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
            <h2 class="text-lg font-bold text-brand-black">Status</h2>
            <div class="flex gap-2">
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-brand-red/10 text-brand-red">{{ $order->statusLabel() }}</span>
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    Payment: {{ $order->paymentStatusLabel() }}
                </span>
            </div>
        </div>
        @if($order->razorpay_order_id)
        <p class="text-xs text-zinc-400">Razorpay Order: <span class="font-mono">{{ $order->razorpay_order_id }}</span></p>
        @endif
        @if($order->razorpay_payment_id)
        <p class="text-xs text-zinc-400 mt-1">Razorpay Payment: <span class="font-mono">{{ $order->razorpay_payment_id }}</span></p>
        @endif
    </div>

    @if($shipment && ($shipment->tracking_url || $shipment->waybill || $shipment->tracking_number))
    <div class="bg-white rounded-2xl border border-zinc-100 p-6 mb-6 shadow-sm">
        <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
            <h2 class="text-lg font-bold text-brand-black">Shipment Tracking</h2>
            @if($shipment->shipment_status)
            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-indigo-50 text-indigo-700">{{ $shipment->statusLabel() }}</span>
            @endif
        </div>

        <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
            @if($shipment->courier_name)
            <div class="flex justify-between sm:block">
                <dt class="text-zinc-500">Courier</dt>
                <dd class="font-semibold text-brand-black sm:mt-0.5">{{ $shipment->courier_name }}</dd>
            </div>
            @endif
            @if($shipment->waybill || $shipment->tracking_number)
            <div class="flex justify-between sm:block">
                <dt class="text-zinc-500">Tracking / AWB No.</dt>
                <dd class="font-semibold text-brand-black sm:mt-0.5 font-mono">{{ $shipment->waybill ?: $shipment->tracking_number }}</dd>
            </div>
            @endif
            @if($shipment->estimated_delivery)
            <div class="flex justify-between sm:block">
                <dt class="text-zinc-500">Estimated Delivery</dt>
                <dd class="font-semibold text-brand-black sm:mt-0.5">{{ $shipment->estimated_delivery->format('F d, Y') }}</dd>
            </div>
            @endif
        </dl>

        @if($shipment->tracking_url)
        <a href="{{ $shipment->tracking_url }}" target="_blank" rel="noopener noreferrer"
           class="mt-5 inline-flex items-center gap-2 bg-brand-red text-white font-bold px-6 py-3 rounded-xl hover:bg-red-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
            Track Shipment
        </a>
        @endif

        @php $scans = $shipment->tracking->sortByDesc('scan_time'); @endphp
        @if($scans->isNotEmpty())
        <div class="mt-6 border-t border-zinc-100 pt-4">
            <h3 class="text-sm font-bold text-brand-black mb-3">Tracking History</h3>
            <ul class="space-y-3">
                @foreach($scans as $scan)
                <li class="flex justify-between gap-4 text-sm">
                    <span class="text-zinc-700">
                        {{ ucwords(str_replace('_', ' ', $scan->status)) }}@if($scan->location) · <span class="text-zinc-500">{{ $scan->location }}</span>@endif
                    </span>
                    <span class="text-zinc-400 whitespace-nowrap">{{ $scan->scan_time?->format('M d, g:i A') }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-zinc-100 p-6 mb-6 shadow-sm">
        <h2 class="text-lg font-bold text-brand-black mb-4">Items</h2>
        <ul class="divide-y divide-zinc-100">
            @foreach($order->items as $item)
            <li class="py-4 flex justify-between">
                <div>
                    <p class="font-semibold text-brand-black">{{ $item->product_name }}</p>
                    <p class="text-sm text-zinc-500">Qty: {{ $item->quantity }} × @money($item->price)</p>
                </div>
                <p class="font-bold">@money($item->subtotal)</p>
            </li>
            @endforeach
        </ul>
        <hr class="my-4 border-zinc-100">
        <div class="flex justify-between text-lg font-black text-brand-black">
            <span>Total</span>
            <span class="text-brand-red">@money($order->displayTotal())</span>
        </div>
    </div>

    @if(isset($reviewableItems) && $reviewableItems->isNotEmpty())
    <div class="bg-white rounded-2xl border border-zinc-100 p-6 mb-6 shadow-sm">
        <h2 class="text-lg font-bold text-brand-black mb-4">Rate Your Purchase</h2>
        <p class="text-sm text-zinc-500 mb-6">Your order was delivered — share your feedback!</p>
        <div class="space-y-8">
            @foreach($reviewableItems as $item)
            <div>
                <p class="font-semibold text-brand-black mb-3">{{ $item->product_name }}</p>
                @if($item->product)
                    <x-review-form :product="$item->product" :order-item-id="$item->id" />
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-zinc-100 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-brand-black mb-4">Shipping Address</h2>
        <p class="text-zinc-600 whitespace-pre-line">{{ $order->shipping_address }}</p>
        @if($order->notes)
        <h3 class="text-sm font-bold text-brand-black mt-4 mb-2">Notes</h3>
        <p class="text-zinc-600">{{ $order->notes }}</p>
        @endif
    </div>
</div>
@endsection
