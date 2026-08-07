@extends('layouts.shop')

@section('title', 'Checkout - ' . config('app.name'))

@php
    $c = $customer;
    $addr = $defaultAddress;
    $fullName = old('shipping.full_name', $addr?->full_name ?? $c?->full_name ?? $user->name);
    $nameParts = explode(' ', $fullName, 2);
    $inputClass = 'checkout-input w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm text-brand-black placeholder:text-zinc-400 focus:border-brand-red focus:ring-1 focus:ring-brand-red';
@endphp

@section('content')
@push('styles')
<style>
    .checkout-input { max-width: 100%; box-sizing: border-box; }
    #checkout-form { width: 100%; max-width: 100%; }
    .checkout-page { padding-bottom: 7rem; }
    @media (min-width: 1024px) {
        .checkout-page { padding-bottom: 0; }
    }
    #mobile-checkout-bar {
        width: 100%;
        max-width: 100vw;
        left: 0;
        right: 0;
    }
</style>
@endpush

<div class="checkout-page bg-zinc-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 w-full">

        {{-- Page header + steps --}}
        <div class="mb-6 sm:mb-8">
            <nav class="flex items-center gap-2 text-xs sm:text-sm text-zinc-500 mb-4">
                <a href="{{ route('cart.index') }}" class="hover:text-brand-red">Cart</a>
                <span>/</span>
                <span class="text-brand-black font-semibold">Checkout</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-black text-brand-black">Checkout</h1>
            <p class="text-sm text-zinc-500 mt-1">Complete your details for delivery and payment</p>

            <div class="flex items-center gap-2 sm:gap-4 mt-5">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center">✓</span>
                    <span class="text-xs sm:text-sm font-medium text-zinc-600 hidden sm:inline">Cart</span>
                </div>
                <div class="flex-1 h-px bg-brand-red max-w-12 sm:max-w-20"></div>
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-brand-red text-white text-xs font-bold flex items-center justify-center">2</span>
                    <span class="text-xs sm:text-sm font-semibold text-brand-black">Details</span>
                </div>
                <div class="flex-1 h-px bg-zinc-200 max-w-12 sm:max-w-20"></div>
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-zinc-200 text-zinc-500 text-xs font-bold flex items-center justify-center">3</span>
                    <span class="text-xs sm:text-sm font-medium text-zinc-400 hidden sm:inline">Payment</span>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form" class="grid lg:grid-cols-12 gap-6 lg:gap-8 w-full min-w-0">
            @csrf

            {{-- LEFT: Form sections --}}
            <div class="lg:col-span-7 space-y-5 min-w-0 order-2 lg:order-1">

                {{-- Contact --}}
                <section class="bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-zinc-100 bg-zinc-50/50">
                        <h2 class="font-bold text-brand-black flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-brand-red/10 text-brand-red text-xs font-black flex items-center justify-center">1</span>
                            Contact Details
                        </h2>
                    </div>
                    <div class="p-4 sm:p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">First Name <span class="text-brand-red">*</span></label>
                                <input name="first_name" value="{{ old('first_name', $c?->first_name ?? $nameParts[0] ?? '') }}" required class="{{ $inputClass }}">
                                @error('first_name')<p class="text-brand-red text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Last Name</label>
                                <input name="last_name" value="{{ old('last_name', $c?->last_name ?? ($nameParts[1] ?? '')) }}" class="{{ $inputClass }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Email <span class="text-brand-red">*</span></label>
                            <input name="email" type="email" value="{{ old('email', $c?->email ?? $user->email) }}" required class="{{ $inputClass }}">
                            @error('email')<p class="text-brand-red text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-[5rem_1fr] sm:grid-cols-[6rem_1fr] gap-3">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Code</label>
                                <input name="country_code" value="{{ old('country_code', $c?->country_code ?? '+91') }}" class="{{ $inputClass }}">
                            </div>
                            <div class="min-w-0">
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Mobile <span class="text-brand-red">*</span></label>
                                <input name="mobile" value="{{ old('mobile', $c?->mobile ?? $user->phone) }}" required class="{{ $inputClass }}" placeholder="10-digit number">
                                @error('mobile')<p class="text-brand-red text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Gender</label>
                            <select name="gender" class="{{ $inputClass }}">
                                <option value="">Select gender</option>
                                @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $v => $l)
                                <option value="{{ $v }}" @selected(old('gender', $c?->gender) === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="flex items-start gap-3 p-3 rounded-xl bg-zinc-50 border border-zinc-100 cursor-pointer hover:border-zinc-200 transition-colors">
                            <input type="checkbox" name="newsletter_subscription" value="1" @checked(old('newsletter_subscription', $c?->newsletter_subscription))
                                class="mt-0.5 shrink-0 rounded border-zinc-300 text-brand-red focus:ring-brand-red">
                            <span class="text-sm text-zinc-600 leading-relaxed">Subscribe to newsletter for offers and updates</span>
                        </label>
                    </div>
                </section>

                {{-- Shipping --}}
                <section class="bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-zinc-100 bg-zinc-50/50">
                        <h2 class="font-bold text-brand-black flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-brand-red/10 text-brand-red text-xs font-black flex items-center justify-center">2</span>
                            Shipping Address
                        </h2>
                    </div>
                    <div class="p-4 sm:p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Address Type</label>
                                <select name="shipping[address_type]" class="{{ $inputClass }}">
                                    @foreach(['home' => 'Home', 'office' => 'Office', 'other' => 'Other'] as $v => $l)
                                    <option value="{{ $v }}" @selected(old('shipping.address_type', $addr?->address_type ?? 'home') === $v)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Receiver Name <span class="text-brand-red">*</span></label>
                                <input name="shipping[full_name]" id="shipping-full-name" value="{{ old('shipping.full_name', $addr?->full_name ?? $c?->full_name ?? $user->name) }}" required class="{{ $inputClass }}">
                                @error('shipping.full_name')<p class="text-brand-red text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Receiver Mobile <span class="text-brand-red">*</span></label>
                                <input name="shipping[mobile]" id="shipping-mobile" value="{{ old('shipping.mobile', $addr?->mobile ?? $c?->mobile ?? $user->phone) }}" required class="{{ $inputClass }}">
                                @error('shipping.mobile')<p class="text-brand-red text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">PIN Code <span class="text-brand-red">*</span></label>
                                <div class="flex gap-2">
                                    <input name="shipping[pincode]" id="shipping-pincode" value="{{ old('shipping.pincode', $addr?->pincode) }}" required maxlength="6" inputmode="numeric"
                                        class="{{ $inputClass }} flex-1 min-w-0" placeholder="6 digits">
                                    <button type="button" id="check-pincode-btn"
                                        class="shrink-0 px-4 py-2.5 rounded-lg bg-zinc-100 text-zinc-700 text-sm font-semibold hover:bg-zinc-200 transition-colors">
                                        Update
                                    </button>
                                </div>
                                @error('shipping.pincode')<p class="text-brand-red text-xs mt-1">{{ $message }}</p>@enderror
                                <div id="pincode-result" class="mt-2 text-sm hidden"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Address Line 1 <span class="text-brand-red">*</span></label>
                            <input name="shipping[address_line_1]" value="{{ old('shipping.address_line_1', $addr?->address_line_1) }}" required class="{{ $inputClass }}" placeholder="House no., building, street">
                            @error('shipping.address_line_1')<p class="text-brand-red text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Address Line 2 <span class="text-zinc-400 font-normal">(optional)</span></label>
                            <input name="shipping[address_line_2]" value="{{ old('shipping.address_line_2', $addr?->address_line_2) }}" class="{{ $inputClass }}" placeholder="Area, colony">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">Landmark <span class="text-zinc-400 font-normal">(optional)</span></label>
                            <input name="shipping[landmark]" value="{{ old('shipping.landmark', $addr?->landmark) }}" class="{{ $inputClass }}" placeholder="Near hospital, mall, etc.">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">City <span class="text-brand-red">*</span></label>
                                <input name="shipping[city]" id="shipping-city" value="{{ old('shipping.city', $addr?->city) }}" required class="{{ $inputClass }}">
                                @error('shipping.city')<p class="text-brand-red text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">District</label>
                                <input name="shipping[district]" id="shipping-district" value="{{ old('shipping.district', $addr?->district) }}" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">State <span class="text-brand-red">*</span></label>
                                <input name="shipping[state]" id="shipping-state" value="{{ old('shipping.state', $addr?->state) }}" required class="{{ $inputClass }}">
                                @error('shipping.state')<p class="text-brand-red text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Country <span class="text-brand-red">*</span></label>
                                <input name="shipping[country]" value="{{ old('shipping.country', $addr?->country ?? 'India') }}" required class="{{ $inputClass }}">
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Billing --}}
                <section class="bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-zinc-100 bg-zinc-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h2 class="font-bold text-brand-black flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-brand-red/10 text-brand-red text-xs font-black flex items-center justify-center">3</span>
                            Billing Address
                        </h2>
                        <label class="flex items-center gap-2 text-sm text-zinc-600 cursor-pointer">
                            <input type="hidden" name="same_billing" value="0">
                            <input type="checkbox" name="same_billing" id="same-billing" value="1" @checked(old('same_billing', '1') != '0')
                                class="rounded border-zinc-300 text-brand-red focus:ring-brand-red">
                            Same as shipping
                        </label>
                    </div>
                    <div id="billing-fields" class="p-4 sm:p-6 {{ old('same_billing', '1') == '0' ? '' : 'hidden' }}">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Address Type</label>
                                <select name="billing[address_type]" class="{{ $inputClass }}">
                                    @foreach(['home' => 'Home', 'office' => 'Office', 'other' => 'Other'] as $v => $l)
                                    <option value="{{ $v }}" @selected(old('billing.address_type', 'home') === $v)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Full Name</label>
                                <input name="billing[full_name]" value="{{ old('billing.full_name') }}" class="{{ $inputClass }} billing-input">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Mobile</label>
                                <input name="billing[mobile]" value="{{ old('billing.mobile') }}" class="{{ $inputClass }} billing-input">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">PIN Code</label>
                                <input name="billing[pincode]" value="{{ old('billing.pincode') }}" maxlength="6" class="{{ $inputClass }} billing-input">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Address Line 1</label>
                                <input name="billing[address_line_1]" value="{{ old('billing.address_line_1') }}" class="{{ $inputClass }} billing-input">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Address Line 2</label>
                                <input name="billing[address_line_2]" value="{{ old('billing.address_line_2') }}" class="{{ $inputClass }} billing-input">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">City</label>
                                <input name="billing[city]" value="{{ old('billing.city') }}" class="{{ $inputClass }} billing-input">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">State</label>
                                <input name="billing[state]" value="{{ old('billing.state') }}" class="{{ $inputClass }} billing-input">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Country</label>
                                <input name="billing[country]" value="{{ old('billing.country', 'India') }}" class="{{ $inputClass }} billing-input">
                            </div>
                        </div>
                    </div>
                    <div id="billing-same-note" class="px-4 sm:px-6 pb-4 sm:pb-6 text-sm text-zinc-500 {{ old('same_billing', '1') == '0' ? 'hidden' : '' }}">
                        Billing address will be same as shipping address.
                    </div>
                </section>

                {{-- Payment --}}
                <section class="bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-zinc-100 bg-zinc-50/50">
                        <h2 class="font-bold text-brand-black flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-brand-red/10 text-brand-red text-xs font-black flex items-center justify-center">4</span>
                            Payment Method
                        </h2>
                    </div>
                    <div class="p-4 sm:p-6 space-y-3">
                        @if($razorpayEnabled ?? true)
                        <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all hover:border-brand-red/30 has-[:checked]:border-brand-red has-[:checked]:bg-red-50/50 {{ ($defaultPayment ?? 'online') === 'online' ? 'border-brand-red bg-red-50/30' : 'border-zinc-100' }}">
                            <input type="radio" name="payment_method" value="online" @checked(old('payment_method', $defaultPayment ?? 'online') === 'online') class="mt-1 shrink-0 text-brand-red focus:ring-brand-red">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-brand-black">Pay Online</p>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide bg-brand-red text-white px-2 py-0.5 rounded">Razorpay</span>
                                    @if($razorpayLive ?? false)
                                    <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Live</span>
                                    @else
                                    <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded">Test mode</span>
                                    @endif
                                </div>
                                <p class="text-sm text-zinc-500 mt-1">UPI · Credit/Debit Card · Net Banking · Wallets</p>
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <span class="text-[10px] font-medium text-zinc-600 bg-zinc-100 px-2 py-0.5 rounded">GPay</span>
                                    <span class="text-[10px] font-medium text-zinc-600 bg-zinc-100 px-2 py-0.5 rounded">PhonePe</span>
                                    <span class="text-[10px] font-medium text-zinc-600 bg-zinc-100 px-2 py-0.5 rounded">Visa</span>
                                    <span class="text-[10px] font-medium text-zinc-600 bg-zinc-100 px-2 py-0.5 rounded">Mastercard</span>
                                </div>
                            </div>
                        </label>
                        @endif

                        @if($codEnabled ?? false)
                        <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all hover:border-brand-red/30 has-[:checked]:border-brand-red has-[:checked]:bg-red-50/50 {{ ($defaultPayment ?? '') === 'cod' ? 'border-brand-red bg-red-50/30' : 'border-zinc-100' }}">
                            <input type="radio" name="payment_method" value="cod" @checked(old('payment_method', $defaultPayment ?? 'cod') === 'cod') class="mt-1 shrink-0 text-brand-red focus:ring-brand-red">
                            <div class="min-w-0">
                                <p class="font-semibold text-brand-black">Cash on Delivery</p>
                                <p class="text-sm text-zinc-500 mt-0.5">Pay when your order is delivered at your doorstep</p>
                            </div>
                        </label>
                        @elseif(!($razorpayEnabled ?? true))
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            Online payment is not configured. Please contact the store or enable Cash on Delivery in admin.
                        </div>
                        @else
                        <p class="text-sm text-zinc-500">Cash on Delivery is temporarily unavailable.</p>
                        @endif
                    </div>
                </section>

                {{-- Notes --}}
                <section class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-4 sm:p-6">
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Order Notes <span class="text-zinc-400 font-normal">(optional)</span></label>
                    <textarea name="notes" rows="2" class="{{ $inputClass }}" placeholder="Any special delivery instructions?">{{ old('notes') }}</textarea>
                </section>
            </div>

            {{-- RIGHT: Order Summary --}}
            <div class="lg:col-span-5 min-w-0 order-1 lg:order-2 mb-2 lg:mb-0">
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm lg:sticky lg:top-24 overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-zinc-100 bg-zinc-50/50">
                        <h2 class="font-bold text-brand-black">Order Summary</h2>
                    </div>
                    <div class="p-4 sm:p-6">
                        <ul class="space-y-3 mb-4">
                            @foreach($items as $item)
                            @php $img = $item->product->displayImage(); @endphp
                            <li class="flex gap-3">
                                <div class="w-14 h-14 rounded-lg bg-zinc-100 shrink-0 overflow-hidden flex items-center justify-center">
                                    @if($img)
                                    <img src="{{ asset('storage/'.$img) }}" alt="" class="w-full h-full object-cover">
                                    @else
                                    <svg class="w-6 h-6 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-brand-black line-clamp-2">{{ $item->product->name }}</p>
                                    <p class="text-xs text-zinc-500 mt-0.5">Qty: {{ $item->quantity }}</p>
                                </div>
                                <p class="text-sm font-semibold text-brand-black shrink-0">@money($item->subtotal())</p>
                            </li>
                            @endforeach
                        </ul>

                        <x-coupon-form :coupons-enabled="$couponsEnabled" :applied-coupon="$appliedCoupon ?? null" :coupon-discount="$couponDiscount ?? 0" />

                        @if($freeShippingEnabled ?? false)
                        <div id="free-shipping-banner" class="mb-4 rounded-xl px-3 py-2.5 text-xs {{ ($freeShippingQualified ?? false) ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                            @if($freeShippingQualified ?? false)
                                🎉 <strong>Free shipping</strong> applied — your order is above @money($freeShippingMinAmount)!
                            @else
                                Add <strong id="free-shipping-remaining">@money($freeShippingRemaining)</strong> more for <strong>free shipping</strong> (orders above @money($freeShippingMinAmount)).
                            @endif
                        </div>
                        @endif

                        <div class="border-t border-zinc-100 pt-4 space-y-2.5">
                            @if($taxSummary['has_exclusive_items'])
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500">Subtotal (excl. GST)</span>
                                <span class="font-medium">@money($taxSummary['subtotal'])</span>
                            </div>
                            @if($taxSummary['checkout_tax_amount'] > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500">{{ $taxLabel }}</span>
                                <span class="font-medium">@money($taxSummary['checkout_tax_amount'])</span>
                            </div>
                            @endif
                            @else
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500">Subtotal</span>
                                <span class="font-medium">@money($taxSummary['items_total'])</span>
                            </div>
                            <p class="text-xs text-emerald-600 bg-emerald-50 rounded-lg px-2.5 py-1.5">✓ GST included in prices</p>
                            @endif
                            <div id="coupon-summary-row" class="flex justify-between text-sm text-emerald-600 {{ ($couponDiscount ?? 0) > 0 ? '' : 'hidden' }}">
                                <span>Coupon (<span id="coupon-code-label">{{ optional($appliedCoupon)->code ?? '' }}</span>)</span>
                                <span id="coupon-discount-amount" class="font-medium">-{{ ($couponDiscount ?? 0) > 0 ? '₹'.number_format($couponDiscount, 2) : '' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500">Shipping</span>
                                <span id="shipping-amount" class="font-medium {{ $shippingCharge === 0 ? 'text-emerald-600' : '' }}">
                                    {{ $shippingCharge === 0 ? 'Free' : '₹'.number_format($shippingCharge, 2) }}
                                </span>
                            </div>
                            <p id="shipping-note" class="text-xs text-zinc-400 {{ in_array($shippingQuote['source'] ?? '', ['free_shipping_threshold', 'free_shipping']) ? '' : 'hidden' }}">
                                {{ $shippingQuote['note'] ?? 'Based on product shipping charges (₹99 default if not set)' }}
                            </p>
                        </div>

                        <div class="hidden lg:flex justify-between items-center mt-4 pt-4 border-t border-zinc-100">
                            <span class="text-base font-bold text-brand-black">Total</span>
                            <span class="text-xl font-black text-brand-red" id="grand-total">@money($grandTotal)</span>
                        </div>

                        <div id="delivery-eta" class="hidden mt-3 text-sm text-emerald-700 bg-emerald-50 rounded-lg px-3 py-2.5 flex items-center gap-2">
                            <span>🚚</span><span id="delivery-eta-text"></span>
                        </div>

                        <button type="submit" class="hidden lg:flex w-full mt-5 bg-brand-red text-white py-3.5 rounded-xl font-bold hover:bg-red-700 transition-colors items-center justify-center gap-2" id="checkout-submit">
                            Place Order
                        </button>
                        <p class="text-xs text-zinc-400 text-center mt-3 hidden" id="online-hint">You'll be redirected to Razorpay to complete payment.</p>

                        <div class="mt-4 flex items-center justify-center gap-4 text-xs text-zinc-400">
                            <span>🔒 Secure</span>
                            <span>✓ Genuine</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Mobile sticky bottom bar --}}
    <div id="mobile-checkout-bar" class="lg:hidden fixed bottom-0 z-50 bg-white border-t border-zinc-200 shadow-[0_-4px_24px_rgba(0,0,0,0.1)]">
        <div class="px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] w-full max-w-full box-border">
            <div class="flex items-center justify-between gap-3 w-full">
                <div class="min-w-0 overflow-hidden">
                    <p class="text-[11px] text-zinc-500 uppercase tracking-wide">Total</p>
                    <p class="text-xl font-black text-brand-red leading-tight truncate" id="mobile-grand-total">@money($grandTotal)</p>
                </div>
                <button type="submit" form="checkout-form"
                    class="shrink-0 bg-brand-red text-white px-5 sm:px-6 py-3 rounded-xl font-bold text-sm whitespace-nowrap min-h-[46px] hover:bg-red-700 active:bg-red-800 transition-colors"
                    id="mobile-checkout-submit">
                    Place Order
                </button>
            </div>
            <p class="text-[10px] text-zinc-400 text-center mt-2 hidden" id="mobile-online-hint">Pay securely via Razorpay</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const form = document.getElementById('checkout-form');
const hint = document.getElementById('online-hint');
const mobileHint = document.getElementById('mobile-online-hint');
const submitBtn = document.getElementById('checkout-submit');
const mobileSubmitBtn = document.getElementById('mobile-checkout-submit');
const sameBilling = document.getElementById('same-billing');
const billingFields = document.getElementById('billing-fields');
const billingSameNote = document.getElementById('billing-same-note');
const pincodeInput = document.getElementById('shipping-pincode');
const checkPincodeBtn = document.getElementById('check-pincode-btn');
const pincodeResult = document.getElementById('pincode-result');
const deliveryEta = document.getElementById('delivery-eta');
const deliveryEtaText = document.getElementById('delivery-eta-text');
const shippingAmount = document.getElementById('shipping-amount');
const shippingNote = document.getElementById('shipping-note');
const grandTotalEl = document.getElementById('grand-total');
const mobileGrandTotalEl = document.getElementById('mobile-grand-total');
const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
const itemsTotal = {{ $taxSummary['items_total'] }};
let couponDiscount = {{ $couponDiscount ?? 0 }};
let currentShippingCharge = {{ $shippingCharge ?? 0 }};
const freeShippingMin = {{ $freeShippingMinAmount ?? 0 }};
const freeShippingEnabled = {{ ($freeShippingEnabled ?? false) ? 'true' : 'false' }};

let pincodeServiceable = true;

function formatMoney(amount) {
    return '₹' + Number(amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updateShippingDisplay(charge, source, note) {
    if (charge === 0) {
        shippingAmount.textContent = 'Free';
        shippingAmount.className = 'font-medium text-emerald-600';
    } else {
        shippingAmount.textContent = formatMoney(charge);
        shippingAmount.className = 'font-medium';
    }
    if (note && (source === 'manual' || source === 'default')) {
        shippingNote.textContent = note;
        shippingNote.classList.remove('hidden');
    } else if (source === 'free_shipping_threshold' && note) {
        shippingNote.textContent = note;
        shippingNote.classList.remove('hidden');
    } else {
        shippingNote.classList.add('hidden');
    }
}

function updateGrandTotal(total) {
    const formatted = formatMoney(total);
    if (grandTotalEl) grandTotalEl.textContent = formatted;
    if (mobileGrandTotalEl) mobileGrandTotalEl.textContent = formatted;
}

function recalcGrandTotal() {
    updateGrandTotal(Math.max(0, itemsTotal - couponDiscount + currentShippingCharge));
}

function updateCouponUI(data) {
    const appliedBox = document.getElementById('coupon-applied');
    const inputWrap = document.getElementById('coupon-input-wrap');
    const summaryRow = document.getElementById('coupon-summary-row');
    const codeLabel = document.getElementById('coupon-code-label');
    const discountAmount = document.getElementById('coupon-discount-amount');
    const appliedCode = document.getElementById('coupon-applied-code');
    const appliedSavings = document.getElementById('coupon-applied-savings');
    const codeInput = document.getElementById('coupon-code-input');

    couponDiscount = Number(data.coupon_discount) || 0;

    if (data.coupon_code && couponDiscount > 0) {
        appliedBox?.classList.remove('hidden');
        inputWrap?.classList.add('hidden');
        summaryRow?.classList.remove('hidden');
        if (appliedCode) appliedCode.textContent = data.coupon_code;
        if (codeLabel) codeLabel.textContent = data.coupon_code;
        if (appliedSavings) appliedSavings.textContent = formatMoney(couponDiscount);
        if (discountAmount) discountAmount.textContent = '-' + formatMoney(couponDiscount);
        if (codeInput) codeInput.value = '';
    } else {
        appliedBox?.classList.add('hidden');
        inputWrap?.classList.remove('hidden');
        summaryRow?.classList.add('hidden');
        couponDiscount = 0;
    }

    recalcGrandTotal();
    updateFreeShippingBanner();
}

function updateFreeShippingBanner() {
    if (!freeShippingEnabled) return;
    const banner = document.getElementById('free-shipping-banner');
    if (!banner) return;
    const payable = Math.max(0, itemsTotal - couponDiscount);
    const remaining = Math.max(0, freeShippingMin - payable);
    const qualified = payable >= freeShippingMin;
    banner.className = 'mb-4 rounded-xl px-3 py-2.5 text-xs ' + (qualified
        ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
        : 'bg-amber-50 text-amber-800 border border-amber-200');
    if (qualified) {
        banner.innerHTML = '🎉 <strong>Free shipping</strong> applied — your order is above ' + formatMoney(freeShippingMin) + '!';
    } else {
        banner.innerHTML = 'Add <strong>' + formatMoney(remaining) + '</strong> more for <strong>free shipping</strong> (orders above ' + formatMoney(freeShippingMin) + ').';
    }
}

function showCouponMessage(message, isError = false) {
    const el = document.getElementById('coupon-message');
    if (!el) return;
    el.textContent = message;
    el.className = 'mt-2 text-xs rounded-lg px-3 py-2 ' + (isError
        ? 'bg-red-50 text-brand-red border border-red-200'
        : 'bg-emerald-50 text-emerald-800 border border-emerald-200');
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 5000);
}

async function applyCoupon() {
    const codeInput = document.getElementById('coupon-code-input');
    const applyBtn = document.getElementById('coupon-apply-btn');
    const code = codeInput?.value?.trim();
    if (!code) {
        showCouponMessage('Please enter a coupon code.', true);
        return;
    }
    applyBtn.disabled = true;
    try {
        const res = await fetch('{{ route('cart.coupon.apply') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ code }),
        });
        const data = await res.json();
        if (data.success) {
            updateCouponUI(data);
            showCouponMessage(data.message, false);
            await refreshShippingQuote();
        } else {
            showCouponMessage(data.message || 'Invalid coupon code.', true);
        }
    } catch (e) {
        showCouponMessage('Could not apply coupon. Please try again.', true);
    } finally {
        applyBtn.disabled = false;
    }
}

async function removeCoupon() {
    try {
        const res = await fetch('{{ route('cart.coupon.remove') }}', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await res.json();
        updateCouponUI({ coupon_code: null, coupon_discount: 0 });
        showCouponMessage(data.message || 'Coupon removed.', false);
        await refreshShippingQuote();
    } catch (e) {
        showCouponMessage('Could not remove coupon.', true);
    }
}

document.getElementById('coupon-apply-btn')?.addEventListener('click', applyCoupon);
document.getElementById('coupon-remove-btn')?.addEventListener('click', removeCoupon);
document.getElementById('coupon-code-input')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); applyCoupon(); }
});

function currentPaymentMethod() {
    return form.querySelector('input[name=payment_method]:checked')?.value || 'online';
}

async function refreshShippingQuote() {
    const pin = pincodeInput.value.replace(/\D/g, '');
    if (pin.length !== 6) return;
    try {
        const res = await fetch('{{ route('checkout.shipping-quote') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ pincode: pin, payment_method: currentPaymentMethod() }),
        });
        const data = await res.json();
        if (data.success) {
            updateShippingDisplay(data.amount, data.source, data.note);
            currentShippingCharge = Number(data.amount) || 0;
            if (typeof data.coupon_discount !== 'undefined') {
                couponDiscount = Number(data.coupon_discount) || 0;
            }
            updateGrandTotal(data.grand_total ?? (itemsTotal - couponDiscount + currentShippingCharge));
        }
    } catch (e) {}
}

function updateHint() {
    const online = form.querySelector('input[name=payment_method]:checked')?.value === 'online';
    const label = online ? 'Pay Online' : 'Place Order';
    const desktopLabel = online ? 'Create Order & Pay Online' : 'Place Order';
    hint?.classList.toggle('hidden', !online);
    mobileHint?.classList.toggle('hidden', !online);
    if (submitBtn) submitBtn.textContent = desktopLabel;
    if (mobileSubmitBtn) mobileSubmitBtn.textContent = label;
}

function toggleBilling() {
    const same = sameBilling.checked;
    billingFields.classList.toggle('hidden', same);
    billingSameNote?.classList.toggle('hidden', !same);
    billingFields.querySelectorAll('.billing-input, select').forEach(el => {
        el.required = !same && el.name.includes('billing[') && !el.name.includes('address_line_2') && !el.name.includes('landmark') && !el.name.includes('district');
    });
}

function syncReceiverName() {
    const first = form.querySelector('[name=first_name]')?.value?.trim() || '';
    const last = form.querySelector('[name=last_name]')?.value?.trim() || '';
    const full = [first, last].filter(Boolean).join(' ');
    const receiver = document.getElementById('shipping-full-name');
    if (receiver && !receiver.dataset.edited) receiver.value = full;
}

function syncReceiverMobile() {
    const mobile = form.querySelector('[name=mobile]')?.value?.trim() || '';
    const receiver = document.getElementById('shipping-mobile');
    if (receiver && !receiver.dataset.edited) receiver.value = mobile;
}

document.getElementById('shipping-full-name')?.addEventListener('input', e => { e.target.dataset.edited = '1'; });
document.getElementById('shipping-mobile')?.addEventListener('input', e => { e.target.dataset.edited = '1'; });
form.querySelector('[name=first_name]')?.addEventListener('input', syncReceiverName);
form.querySelector('[name=last_name]')?.addEventListener('input', syncReceiverName);
form.querySelector('[name=mobile]')?.addEventListener('input', syncReceiverMobile);

sameBilling?.addEventListener('change', toggleBilling);
form.querySelectorAll('input[name=payment_method]').forEach(el => el.addEventListener('change', () => {
    updateHint();
    refreshShippingQuote();
}));
updateHint();
toggleBilling();

async function checkPincode() {
    const pin = pincodeInput.value.replace(/\D/g, '');
    if (pin.length !== 6) {
        pincodeResult.className = 'mt-2 text-xs text-brand-red';
        pincodeResult.textContent = 'Enter a valid 6-digit pincode.';
        pincodeResult.classList.remove('hidden');
        return;
    }

    checkPincodeBtn.disabled = true;
    checkPincodeBtn.textContent = '…';
    pincodeResult.className = 'mt-2 text-xs text-zinc-500';
    pincodeResult.textContent = 'Updating shipping…';
    pincodeResult.classList.remove('hidden');

    try {
        const res = await fetch('{{ route('checkout.check-pincode') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ pincode: pin, payment_method: currentPaymentMethod() }),
        });
        const data = await res.json();
        pincodeServiceable = true;

        if (typeof data.shipping_charge !== 'undefined') {
            currentShippingCharge = Number(data.shipping_charge) || 0;
            updateShippingDisplay(data.shipping_charge, data.shipping_source, data.shipping_note);
            updateGrandTotal(itemsTotal - couponDiscount + currentShippingCharge);
        }

        pincodeResult.className = 'mt-2 text-xs text-emerald-700 bg-emerald-50 rounded-lg px-3 py-2';
        pincodeResult.textContent = '✓ Pincode saved. Shipping is calculated from product charges.';
        if (data.estimated_delivery_days) {
            deliveryEtaText.textContent = 'Estimated delivery in ' + data.estimated_delivery_days + ' business days';
            deliveryEta.classList.remove('hidden');
        }
    } catch (e) {
        pincodeResult.className = 'mt-2 text-xs text-brand-red';
        pincodeResult.textContent = 'Could not update shipping. Please try again.';
    }

    checkPincodeBtn.disabled = false;
    checkPincodeBtn.textContent = 'Update';
}

checkPincodeBtn?.addEventListener('click', checkPincode);
pincodeInput?.addEventListener('blur', () => {
    if (pincodeInput.value.replace(/\D/g, '').length === 6) refreshShippingQuote();
});
</script>
@endpush
