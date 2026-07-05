@extends('layouts.shop')

@section('title', 'Shopping Cart - ' . config('app.name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full max-w-full overflow-x-clip">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

    @if($items->isEmpty())
        <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-gray-500 text-lg mb-4">Your cart is empty</p>
            <a href="{{ route('products.index') }}" class="inline-block bg-orange-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-orange-700">Continue Shopping</a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                @foreach($items as $item)
                    <div class="bg-white rounded-xl border border-gray-100 p-6 flex gap-4">
                        <div class="w-24 h-24 bg-gray-100 rounded-lg shrink-0 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <a href="{{ route('products.show', $item->product) }}" class="font-semibold text-gray-900 hover:text-orange-600">{{ $item->product->name }}</a>
                            <p class="text-sm text-gray-500 mt-1">{{ $item->product->category->name }}</p>
                            <p class="text-lg font-bold text-gray-900 mt-2">@money($item->product->price)</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="{{ $item->product->stock }}" class="w-16 rounded border-gray-300 text-sm" onchange="this.form.submit()">
                            </form>
                            <p class="font-semibold">@money($item->subtotal())</p>
                            <form action="{{ route('cart.destroy', $item) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-700">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-6 h-fit">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                @if($taxSummary['has_exclusive_items'])
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Subtotal (excl. GST)</span>
                    <span class="font-semibold">@money($taxSummary['subtotal'])</span>
                </div>
                @if($taxSummary['checkout_tax_amount'] > 0)
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">{{ $taxLabel }}</span>
                    <span class="font-semibold">@money($taxSummary['checkout_tax_amount'])</span>
                </div>
                @endif
                @if($taxSummary['has_inclusive_items'])
                <p class="text-xs text-gray-500 mb-2">Some items include GST in price; others add GST at checkout.</p>
                @endif
                @else
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-semibold">@money($taxSummary['items_total'])</span>
                </div>
                <p class="text-xs text-gray-500 mb-2">All prices include GST</p>
                @endif
                @if($freeShippingEnabled ?? false)
                <div class="mb-3 rounded-lg px-3 py-2.5 text-xs {{ ($freeShippingQualified ?? false) ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                    @if($freeShippingQualified ?? false)
                        🎉 You qualify for <strong>FREE shipping</strong>!
                    @else
                        Add <strong>@money($freeShippingRemaining)</strong> more for free shipping on orders above @money($freeShippingMinAmount).
                    @endif
                </div>
                @endif
                <p class="text-xs text-gray-500 mt-2">Apply coupon codes at checkout. Shipping calculated at checkout based on your pincode.</p>
                <hr class="my-4">
                <div class="flex justify-between mb-6">
                    <span class="text-lg font-semibold">Subtotal</span>
                    <span class="text-lg font-bold">@money($grandTotal)</span>
                </div>
                @auth
                    <a href="{{ route('checkout.index') }}" class="block w-full bg-orange-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-orange-700">Proceed to Checkout</a>
                @else
                    <a href="{{ route('login') }}" class="block w-full bg-orange-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-orange-700">Login to Checkout</a>
                    <p class="text-sm text-gray-500 text-center mt-2">or <a href="{{ route('register') }}" class="text-orange-600 hover:underline">create an account</a></p>
                @endauth
            </div>
        </div>
    @endif
</div>
@endsection
