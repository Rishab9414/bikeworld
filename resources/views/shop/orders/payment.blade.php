@extends('layouts.shop')

@section('title', 'Pay for Order ' . $order->order_number . ' - ' . config('app.name'))

@section('content')
<div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-brand-red/10 text-brand-red mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        <h1 class="text-2xl font-black text-brand-black">Complete Payment</h1>
        <p class="text-zinc-500 mt-2">Order <span class="font-semibold text-brand-black">{{ $order->order_number }}</span></p>
    </div>

    <div class="bg-white rounded-2xl border border-zinc-100 p-6 shadow-sm mb-6">
        <div class="flex justify-between items-center mb-4">
            <span class="text-zinc-500">Amount to pay</span>
            <span class="text-2xl font-black text-brand-red">@money($order->displayTotal())</span>
        </div>
        <div class="text-xs text-zinc-400 space-y-1 border-t border-zinc-100 pt-4">
            <p>Our order ID: <span class="font-mono text-zinc-600">{{ $order->order_number }}</span></p>
            <p>Razorpay order ID: <span class="font-mono text-zinc-600">{{ $order->razorpay_order_id }}</span></p>
            <p>Payment status: <span class="font-semibold text-amber-600">{{ ucfirst($order->payment_status) }}</span></p>
        </div>
    </div>

    @if($checkout['mock'])
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 text-sm text-amber-800">
        <strong>Test mode:</strong> Razorpay keys not configured. Use the button below to simulate a successful payment.
    </div>
    <form action="{{ route('orders.payment.verify') }}" method="POST">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">
        <input type="hidden" name="razorpay_order_id" value="{{ $order->razorpay_order_id }}">
        <input type="hidden" name="razorpay_payment_id" value="pay_mock_{{ strtolower(\Illuminate\Support\Str::random(10)) }}">
        <button type="submit" class="w-full bg-brand-red text-white py-4 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-lg shadow-brand-red/30">
            Simulate Payment (Test Mode)
        </button>
    </form>
    @else
    <button type="button" id="rzp-pay-btn" class="w-full bg-brand-red text-white py-4 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-lg shadow-brand-red/30">
        Pay @money($order->displayTotal()) with Razorpay
    </button>
    @endif

    <a href="{{ route('orders.show', $order) }}" class="block text-center text-sm text-zinc-500 hover:text-brand-red mt-4">View order details</a>
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
