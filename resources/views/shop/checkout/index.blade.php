@extends('layouts.shop')

@section('title', 'Checkout - ' . config('app.name'))

@php
    $c = $customer;
    $addr = $defaultAddress;
    $fullName = old('shipping.full_name', $addr?->full_name ?? $c?->full_name ?? $user->name);
    $nameParts = explode(' ', $fullName, 2);
@endphp

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-black text-brand-black mb-2">Checkout</h1>
    <p class="text-zinc-500 mb-8">Complete your details for delivery and payment</p>

    @if(session('error'))
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form" class="grid lg:grid-cols-3 gap-8">
        @csrf

        <div class="lg:col-span-2 space-y-6">
            {{-- Contact / Customer Details --}}
            <div class="bg-white rounded-2xl border border-zinc-100 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-brand-black mb-4">Contact Details</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">First Name <span class="text-brand-red">*</span></label>
                        <input name="first_name" value="{{ old('first_name', $c?->first_name ?? $nameParts[0] ?? '') }}" required
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                        @error('first_name')<p class="text-brand-red text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Last Name</label>
                        <input name="last_name" value="{{ old('last_name', $c?->last_name ?? ($nameParts[1] ?? '')) }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Email <span class="text-brand-red">*</span></label>
                        <input name="email" type="email" value="{{ old('email', $c?->email ?? $user->email) }}" required
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                        @error('email')<p class="text-brand-red text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-2">
                        <div class="w-24">
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Code</label>
                            <input name="country_code" value="{{ old('country_code', $c?->country_code ?? '+91') }}"
                                class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Mobile <span class="text-brand-red">*</span></label>
                            <input name="mobile" value="{{ old('mobile', $c?->mobile ?? $user->phone) }}" required
                                class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red" placeholder="10-digit number">
                            @error('mobile')<p class="text-brand-red text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Gender</label>
                        <select name="gender" class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                            <option value="">— Select —</option>
                            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('gender', $c?->gender) === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-zinc-700">
                            <input type="checkbox" name="newsletter_subscription" value="1" @checked(old('newsletter_subscription', $c?->newsletter_subscription))
                                class="rounded text-brand-red focus:ring-brand-red">
                            Subscribe to newsletter for offers and updates
                        </label>
                    </div>
                </div>
            </div>

            {{-- Shipping Address --}}
            <div class="bg-white rounded-2xl border border-zinc-100 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-brand-black mb-4">Shipping Address</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Address Type</label>
                        <select name="shipping[address_type]" class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                            @foreach(['home' => 'Home', 'office' => 'Office', 'other' => 'Other'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('shipping.address_type', $addr?->address_type ?? 'home') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Receiver Name <span class="text-brand-red">*</span></label>
                        <input name="shipping[full_name]" id="shipping-full-name" value="{{ old('shipping.full_name', $addr?->full_name ?? $c?->full_name ?? $user->name) }}" required
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                        @error('shipping.full_name')<p class="text-brand-red text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Receiver Mobile <span class="text-brand-red">*</span></label>
                        <input name="shipping[mobile]" id="shipping-mobile" value="{{ old('shipping.mobile', $addr?->mobile ?? $c?->mobile ?? $user->phone) }}" required
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                        @error('shipping.mobile')<p class="text-brand-red text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">PIN Code <span class="text-brand-red">*</span></label>
                        <div class="flex gap-2">
                            <input name="shipping[pincode]" id="shipping-pincode" value="{{ old('shipping.pincode', $addr?->pincode) }}" required maxlength="6" inputmode="numeric"
                                class="flex-1 rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red" placeholder="6 digits">
                            <button type="button" id="check-pincode-btn"
                                class="shrink-0 px-4 py-2 rounded-xl border border-brand-red text-brand-red text-sm font-semibold hover:bg-brand-red/5">
                                Check
                            </button>
                        </div>
                        @error('shipping.pincode')<p class="text-brand-red text-sm mt-1">{{ $message }}</p>@enderror
                        <div id="pincode-result" class="mt-2 text-sm hidden"></div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Address Line 1 <span class="text-brand-red">*</span></label>
                        <input name="shipping[address_line_1]" value="{{ old('shipping.address_line_1', $addr?->address_line_1) }}" required
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red" placeholder="House no., building, street">
                        @error('shipping.address_line_1')<p class="text-brand-red text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Address Line 2</label>
                        <input name="shipping[address_line_2]" value="{{ old('shipping.address_line_2', $addr?->address_line_2) }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red" placeholder="Area, colony (optional)">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Landmark</label>
                        <input name="shipping[landmark]" value="{{ old('shipping.landmark', $addr?->landmark) }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red" placeholder="Near hospital, mall, etc.">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">City <span class="text-brand-red">*</span></label>
                        <input name="shipping[city]" id="shipping-city" value="{{ old('shipping.city', $addr?->city) }}" required
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                        @error('shipping.city')<p class="text-brand-red text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">District</label>
                        <input name="shipping[district]" id="shipping-district" value="{{ old('shipping.district', $addr?->district) }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">State <span class="text-brand-red">*</span></label>
                        <input name="shipping[state]" id="shipping-state" value="{{ old('shipping.state', $addr?->state) }}" required
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                        @error('shipping.state')<p class="text-brand-red text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Country <span class="text-brand-red">*</span></label>
                        <input name="shipping[country]" value="{{ old('shipping.country', $addr?->country ?? 'India') }}" required
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                    </div>
                </div>
            </div>

            {{-- Billing Address --}}
            <div class="bg-white rounded-2xl border border-zinc-100 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-brand-black">Billing Address</h2>
                    <label class="flex items-center gap-2 text-sm text-zinc-600">
                        <input type="hidden" name="same_billing" value="0">
                        <input type="checkbox" name="same_billing" id="same-billing" value="1" @checked(old('same_billing', '1') != '0')
                            class="rounded text-brand-red focus:ring-brand-red">
                        Same as shipping
                    </label>
                </div>
                <div id="billing-fields" class="grid sm:grid-cols-2 gap-4 {{ old('same_billing', '1') == '0' ? '' : 'hidden' }}">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Address Type</label>
                        <select name="billing[address_type]" class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red">
                            @foreach(['home' => 'Home', 'office' => 'Office', 'other' => 'Other'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('billing.address_type', 'home') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Full Name</label>
                        <input name="billing[full_name]" value="{{ old('billing.full_name') }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red billing-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Mobile</label>
                        <input name="billing[mobile]" value="{{ old('billing.mobile') }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red billing-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">PIN Code</label>
                        <input name="billing[pincode]" value="{{ old('billing.pincode') }}" maxlength="6"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red billing-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Address Line 1</label>
                        <input name="billing[address_line_1]" value="{{ old('billing.address_line_1') }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red billing-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Address Line 2</label>
                        <input name="billing[address_line_2]" value="{{ old('billing.address_line_2') }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red billing-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Landmark</label>
                        <input name="billing[landmark]" value="{{ old('billing.landmark') }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red billing-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">City</label>
                        <input name="billing[city]" value="{{ old('billing.city') }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red billing-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">District</label>
                        <input name="billing[district]" value="{{ old('billing.district') }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red billing-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">State</label>
                        <input name="billing[state]" value="{{ old('billing.state') }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red billing-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Country</label>
                        <input name="billing[country]" value="{{ old('billing.country', 'India') }}"
                            class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red billing-input">
                    </div>
                </div>
            </div>

            {{-- Order Notes --}}
            <div class="bg-white rounded-2xl border border-zinc-100 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-brand-black mb-4">Order Notes <span class="text-sm font-normal text-zinc-500">(optional)</span></h2>
                <textarea name="notes" rows="2" class="w-full rounded-xl border-zinc-200 focus:border-brand-red focus:ring-brand-red" placeholder="Any special instructions?">{{ old('notes') }}</textarea>
            </div>

            {{-- Payment Method --}}
            <div class="bg-white rounded-2xl border border-zinc-100 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-brand-black mb-4">Payment Method</h2>
                <div class="space-y-3">
                    @if($codEnabled)
                    <label class="flex items-start gap-3 p-4 rounded-xl border border-zinc-200 cursor-pointer hover:border-brand-red/50 has-[:checked]:border-brand-red has-[:checked]:bg-brand-red/5">
                        <input type="radio" name="payment_method" value="cod" @checked(old('payment_method', 'cod') === 'cod') class="mt-1 text-brand-red focus:ring-brand-red">
                        <div>
                            <p class="font-semibold text-brand-black">Cash on Delivery (COD)</p>
                            <p class="text-sm text-zinc-500">Pay when your order is delivered</p>
                        </div>
                    </label>
                    @endif
                    <label class="flex items-start gap-3 p-4 rounded-xl border border-zinc-200 cursor-pointer hover:border-brand-red/50 has-[:checked]:border-brand-red has-[:checked]:bg-brand-red/5">
                        <input type="radio" name="payment_method" value="online" @checked(old('payment_method', $codEnabled ? 'cod' : 'online') === 'online') class="mt-1 text-brand-red focus:ring-brand-red">
                        <div>
                            <p class="font-semibold text-brand-black">Online Payment (Razorpay)</p>
                            <p class="text-sm text-zinc-500">UPI, cards, netbanking — order saved first, then pay securely</p>
                        </div>
                    </label>
                </div>
                @unless($codEnabled)
                <p class="text-sm text-zinc-500 mt-3">Cash on Delivery is temporarily unavailable. Online payment only.</p>
                @endunless
            </div>
        </div>

        {{-- Order Summary Sidebar --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-zinc-100 p-6 shadow-sm sticky top-24">
                <h2 class="text-lg font-bold text-brand-black mb-4">Order Summary</h2>
                <ul class="space-y-3 mb-4 max-h-48 overflow-y-auto">
                    @foreach($items as $item)
                    <li class="flex justify-between text-sm gap-2">
                        <span class="text-zinc-600 truncate">{{ $item->product->name }} × {{ $item->quantity }}</span>
                        <span class="font-medium shrink-0">@money($item->subtotal())</span>
                    </li>
                    @endforeach
                </ul>
                <hr class="my-4 border-zinc-100">
                <div class="flex justify-between text-sm text-zinc-600 mb-2"><span>Subtotal (excl. GST)</span><span>@money($taxSummary['subtotal'])</span></div>
                @if($taxSummary['tax_amount'] > 0)
                <div class="flex justify-between text-sm text-zinc-600 mb-2"><span>{{ $taxLabel }}</span><span>@money($tax)</span></div>
                @endif
                @if($taxSummary['has_inclusive_items'] && $taxSummary['has_exclusive_items'])
                <p class="text-xs text-zinc-400 mb-2">Some items include GST in price; others add GST at checkout.</p>
                @elseif($taxSummary['has_inclusive_items'])
                <p class="text-xs text-zinc-400 mb-2">GST is included in product prices.</p>
                @endif
                <div class="flex justify-between text-sm text-zinc-600 mb-2">
                    <span>Shipping</span>
                    <span class="{{ $shippingCharge === 0 ? 'text-green-600 font-medium' : '' }}">
                        {{ $shippingCharge === 0 ? 'Free' : '₹'.number_format($shippingCharge, 2) }}
                    </span>
                </div>
                <div id="delivery-eta" class="hidden text-sm text-emerald-700 bg-emerald-50 rounded-lg px-3 py-2 mb-4"></div>
                <div class="flex justify-between text-lg font-black text-brand-black mb-6">
                    <span>Total</span>
                    <span class="text-brand-red">@money($grandTotal)</span>
                </div>
                <button type="submit" class="w-full bg-brand-red text-white py-4 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-lg shadow-brand-red/30" id="checkout-submit">
                    Place Order
                </button>
                <p class="text-xs text-zinc-400 text-center mt-3" id="online-hint" style="display:none">Your order will be created first. You'll be redirected to Razorpay to complete payment.</p>
                @if($subtotal < 2000)
                <p class="text-xs text-zinc-500 text-center mt-2">Add @money(2000 - $subtotal) more for free shipping</p>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const form = document.getElementById('checkout-form');
const hint = document.getElementById('online-hint');
const submitBtn = document.getElementById('checkout-submit');
const sameBilling = document.getElementById('same-billing');
const billingFields = document.getElementById('billing-fields');
const pincodeInput = document.getElementById('shipping-pincode');
const checkPincodeBtn = document.getElementById('check-pincode-btn');
const pincodeResult = document.getElementById('pincode-result');
const deliveryEta = document.getElementById('delivery-eta');
const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

let pincodeServiceable = false;

function updateHint() {
    const online = form.querySelector('input[name=payment_method]:checked')?.value === 'online';
    hint.style.display = online ? 'block' : 'none';
    submitBtn.textContent = online ? 'Create Order & Pay Online' : 'Place Order';
}

function toggleBilling() {
    const same = sameBilling.checked;
    billingFields.classList.toggle('hidden', same);
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
form.querySelectorAll('input[name=payment_method]').forEach(el => el.addEventListener('change', updateHint));
updateHint();
toggleBilling();

async function checkPincode() {
    const pin = pincodeInput.value.replace(/\D/g, '');
    if (pin.length !== 6) {
        pincodeResult.className = 'mt-2 text-sm text-brand-red';
        pincodeResult.textContent = 'Enter a valid 6-digit pincode.';
        pincodeResult.classList.remove('hidden');
        pincodeServiceable = false;
        deliveryEta.classList.add('hidden');
        return;
    }

    checkPincodeBtn.disabled = true;
    checkPincodeBtn.textContent = '…';
    pincodeResult.className = 'mt-2 text-sm text-zinc-500';
    pincodeResult.textContent = 'Checking delivery…';
    pincodeResult.classList.remove('hidden');

    try {
        const res = await fetch('{{ route('checkout.check-pincode') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ pincode: pin }),
        });
        const data = await res.json();

        pincodeServiceable = !!data.serviceable;

        if (data.serviceable) {
            pincodeResult.className = 'mt-2 text-sm text-emerald-700 bg-emerald-50 rounded-lg px-3 py-2';
            pincodeResult.innerHTML = '✓ ' + (data.message || 'Delivery available') +
                (data.city ? '<br><span class="text-emerald-600">' + data.city + (data.state ? ', ' + data.state : '') + '</span>' : '');
            deliveryEta.classList.remove('hidden');
            deliveryEta.textContent = '🚚 Expected by ' + (data.estimated_delivery_date || (data.estimated_delivery_days + ' days'));

            if (data.city && !document.getElementById('shipping-city').value) {
                document.getElementById('shipping-city').value = data.city;
            }
            if (data.state && !document.getElementById('shipping-state').value) {
                document.getElementById('shipping-state').value = data.state;
            }
            if (data.district && !document.getElementById('shipping-district').value) {
                document.getElementById('shipping-district').value = data.district;
            }
        } else {
            pincodeResult.className = 'mt-2 text-sm text-brand-red bg-red-50 rounded-lg px-3 py-2';
            pincodeResult.textContent = data.message || 'Delivery not available for this pincode.';
            deliveryEta.classList.add('hidden');
        }
    } catch (e) {
        pincodeResult.className = 'mt-2 text-sm text-brand-red';
        pincodeResult.textContent = 'Could not check pincode. Please try again.';
        pincodeServiceable = false;
    }

    checkPincodeBtn.disabled = false;
    checkPincodeBtn.textContent = 'Check';
}

checkPincodeBtn?.addEventListener('click', checkPincode);
pincodeInput?.addEventListener('blur', () => {
    if (pincodeInput.value.replace(/\D/g, '').length === 6) checkPincode();
});

form.addEventListener('submit', e => {
    const pin = pincodeInput.value.replace(/\D/g, '');
    if (pin.length === 6 && !pincodeServiceable) {
        e.preventDefault();
        alert('Please check delivery availability for your pincode before placing the order.');
        checkPincode();
    }
});

@if(old('shipping.pincode', $addr?->pincode))
document.addEventListener('DOMContentLoaded', checkPincode);
@endif
</script>
@endpush
