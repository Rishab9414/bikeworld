@extends('layouts.shop')

@section('title', 'Order Confirmed — ' . $order->order_number)

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-10">
        <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-3xl font-black text-brand-black mb-2">Thank you for your order!</h1>
        <p class="text-zinc-600">Order <span class="font-bold text-brand-black">{{ $order->order_number }}</span> has been placed successfully.</p>
    </div>

    <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 mb-6">
        <div class="flex flex-wrap justify-between gap-3 mb-6 pb-6 border-b border-zinc-100">
            <div>
                <p class="text-xs text-zinc-500 uppercase tracking-wide font-semibold">Payment</p>
                <p class="font-bold text-brand-black mt-1">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online (Razorpay)' }}</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500 uppercase tracking-wide font-semibold">Status</p>
                <p class="font-bold text-brand-red mt-1">{{ $order->statusLabel() }}</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500 uppercase tracking-wide font-semibold">Total</p>
                <p class="font-black text-xl text-brand-black mt-1">@money($order->displayTotal())</p>
            </div>
        </div>

        <h2 class="font-bold text-brand-black mb-3">Order Summary</h2>
        <ul class="divide-y divide-zinc-100 mb-4">
            @foreach($order->items as $item)
            <li class="py-3 flex justify-between text-sm">
                <span class="text-zinc-700">{{ $item->product_name }} × {{ $item->quantity }}</span>
                <span class="font-semibold">@money($item->subtotal)</span>
            </li>
            @endforeach
        </ul>

        @if($order->expected_delivery)
        <p class="text-sm text-zinc-600 bg-zinc-50 rounded-xl px-4 py-3">
            <span class="font-semibold text-brand-black">Estimated delivery:</span> {{ $order->expected_delivery->format('d M Y') }}
        </p>
        @endif
    </div>

    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 mb-8 text-sm text-blue-900">
        <p class="font-bold mb-2">What happens next?</p>
        <ul class="space-y-1.5 list-disc list-inside text-blue-800">
            <li>You'll receive email updates at every step.</li>
            <li>We prepare and pack your order with care.</li>
            <li>Track your order anytime from My Orders.</li>
        </ul>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ route('orders.show', $order) }}" class="inline-flex justify-center bg-brand-red text-white font-bold px-8 py-3.5 rounded-xl hover:bg-red-700 transition-colors">
            View Order Details
        </a>
        <a href="{{ route('products.index') }}" class="inline-flex justify-center border border-zinc-200 text-brand-black font-bold px-8 py-3.5 rounded-xl hover:border-brand-red hover:text-brand-red transition-colors">
            Continue Shopping
        </a>
    </div>
</div>
@endsection
