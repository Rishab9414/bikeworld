@extends('layouts.shop')

@section('title', 'Secure Payment — ' . $order->order_number)

@section('content')
<div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- Progress steps --}}
    <div class="flex items-center justify-center gap-2 sm:gap-3 mb-8 text-xs sm:text-sm">
        <div class="flex items-center gap-1.5 text-emerald-600 font-semibold">
            <span class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </span>
            Order placed
        </div>
        <span class="w-8 h-px bg-zinc-200"></span>
        <div class="flex items-center gap-1.5 text-brand-red font-bold">
            <span class="w-6 h-6 rounded-full bg-brand-red text-white flex items-center justify-center text-xs">2</span>
            Pay now
        </div>
        <span class="w-8 h-px bg-zinc-200"></span>
        <div class="flex items-center gap-1.5 text-zinc-400">
            <span class="w-6 h-6 rounded-full bg-zinc-100 flex items-center justify-center text-xs">3</span>
            Done
        </div>
    </div>

    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-red/10 text-brand-red mb-4">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <h1 class="text-2xl font-black text-brand-black">Secure Payment</h1>
        <p class="text-zinc-500 mt-2 text-sm">Your order is saved. Complete payment to confirm it.</p>
    </div>

    @if(session('error'))
    <div class="mb-5 rounded-xl bg-red-50 border border-red-200 text-brand-red px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden mb-6">
        <div class="bg-zinc-50 px-5 py-3 border-b border-zinc-100 flex items-center justify-between">
            <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">Order summary</span>
            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                Awaiting payment
            </span>
        </div>
        <div class="p-5">
            <div class="flex justify-between items-start gap-4 mb-4">
                <div>
                    <p class="font-bold text-brand-black">{{ $order->order_number }}</p>
                    <p class="text-xs text-zinc-400 mt-0.5">{{ $order->created_at->format('M d, Y · h:i A') }}</p>
                </div>
                <p class="text-2xl font-black text-brand-red shrink-0">@money($order->displayTotal())</p>
            </div>
            <div class="grid grid-cols-2 gap-3 text-xs text-zinc-500 border-t border-zinc-100 pt-4">
                <div>
                    <p class="text-zinc-400 mb-0.5">Payment method</p>
                    <p class="font-semibold text-zinc-700">Online (Razorpay)</p>
                </div>
                <div>
                    <p class="text-zinc-400 mb-0.5">Items</p>
                    <p class="font-semibold text-zinc-700">{{ $order->items_count }} product(s)</p>
                </div>
            </div>
        </div>
    </div>

    @if($checkout['mock'])
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 text-sm text-amber-800">
        <strong>Test mode:</strong> Payment will be simulated. Add live Razorpay keys in <code class="text-xs">.env</code> for production.
    </div>
    <form action="{{ route('orders.payment.verify') }}" method="POST">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">
        <input type="hidden" name="razorpay_order_id" value="{{ $order->razorpay_order_id }}">
        <input type="hidden" name="razorpay_payment_id" value="pay_mock_{{ strtolower(\Illuminate\Support\Str::random(10)) }}">
        <button type="submit" class="w-full bg-brand-red text-white py-4 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-lg shadow-brand-red/25 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Confirm payment — @money($order->displayTotal())
        </button>
    </form>
    @else
    <button type="button" id="rzp-pay-btn" class="w-full bg-brand-red text-white py-4 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-lg shadow-brand-red/25 flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        Pay @money($order->displayTotal()) securely
    </button>
    @endif

    <div class="mt-5 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-xs text-zinc-400">
        <span class="inline-flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            256-bit SSL encrypted
        </span>
        <span class="inline-flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Powered by Razorpay
        </span>
    </div>

    <a href="{{ route('orders.show', $order) }}" class="block text-center text-sm text-zinc-500 hover:text-brand-red mt-6">View order details</a>
</div>
@endsection

@if(!$checkout['mock'])
@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('rzp-pay-btn').addEventListener('click', function () {
    const options = {
        key: @json($checkout['key']),
        amount: @json($checkout['amount']),
        currency: @json($checkout['currency']),
        name: @json($checkout['name']),
        description: @json($checkout['description']),
        order_id: @json($checkout['order_id']),
        prefill: @json($checkout['prefill']),
        theme: @json($checkout['theme']),
        handler: function (response) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = @json(route('orders.payment.verify'));
            const fields = {
                _token: @json(csrf_token()),
                order_id: @json($order->id),
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature,
            };
            Object.entries(fields).forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        },
        modal: {
            ondismiss: function () {
                alert('Payment cancelled. Your order is saved — you can pay anytime from order details.');
            }
        }
    };
    new Razorpay(options).open();
});
</script>
@endpush
@endif
