@extends('admin.layouts.app')
@php $o = $order; $ship = $o->shipment ?? $o->shipmentRecord; @endphp
@section('title', 'Order '.$o->order_number)
@section('page-title', 'Order '.$o->order_number)
@section('page-subtitle', $o->created_at->format('M d, Y H:i'))

@section('content')
<div class="grid xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        {{-- Products --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-bold text-lg mb-4">Products</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-slate-500 border-b"><tr>
                        <th class="pb-2">Product</th><th class="pb-2">SKU</th><th class="pb-2">Qty</th><th class="pb-2">Price</th><th class="pb-2">GST</th><th class="pb-2">Total</th>
                    </tr></thead>
                    <tbody>
                    @foreach($o->items as $item)
                    <tr class="border-b border-slate-50">
                        <td class="py-3">
                            <div class="flex items-center gap-3">
                                @if($item->product?->displayImage())<img src="{{ asset('storage/'.$item->product->displayImage()) }}" class="w-10 h-10 rounded-lg object-cover">@endif
                                <span class="font-medium">{{ $item->product_name }}</span>
                            </div>
                        </td>
                        <td>{{ $item->sku ?? '—' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹{{ number_format($item->price, 2) }}</td>
                        <td>₹{{ number_format($item->gst, 2) }}</td>
                        <td class="font-semibold">₹{{ number_format($item->lineTotal(), 2) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 pt-4 border-t text-sm space-y-1 max-w-xs ml-auto">
                <div class="flex justify-between"><span>Subtotal</span><span>₹{{ number_format($o->subtotal, 2) }}</span></div>
                <div class="flex justify-between"><span>Shipping</span><span>₹{{ number_format($o->shipping_charge, 2) }}</span></div>
                <div class="flex justify-between"><span>Tax (GST)</span><span>₹{{ number_format($o->tax_amount, 2) }}</span></div>
                @if($o->coupon_discount > 0)
                <div class="flex justify-between text-emerald-600"><span>Coupon @if($o->coupon_code)({{ $o->coupon_code }})@endif</span><span>-₹{{ number_format($o->coupon_discount, 2) }}</span></div>
                @elseif($o->discount > 0)
                <div class="flex justify-between text-emerald-600"><span>Discount</span><span>-₹{{ number_format($o->discount, 2) }}</span></div>
                @endif
                <div class="flex justify-between font-bold text-base pt-2 border-t"><span>Grand Total</span><span>₹{{ number_format($o->displayTotal(), 2) }}</span></div>
            </div>
        </div>

        {{-- Addresses --}}
        <div class="grid md:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl border p-5"><h4 class="font-bold mb-2">Billing Address</h4><p class="text-sm text-slate-600 whitespace-pre-line">{{ $o->billing_address }}</p></div>
            <div class="bg-white rounded-2xl border p-5"><h4 class="font-bold mb-2">Shipping Address</h4><p class="text-sm text-slate-600 whitespace-pre-line">{{ $o->shipping_address }}</p></div>
        </div>

        {{-- Manual Shipment --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-bold text-lg mb-1">Manual Shipment</h3>
            <p class="text-xs text-slate-400 mb-4">Manage shipping yourself — enter courier and tracking details, then generate label &amp; invoice.</p>

            @if($ship)
            <dl class="grid sm:grid-cols-2 gap-3 text-sm mb-5">
                <div><dt class="text-slate-500">Courier</dt><dd class="font-medium">{{ $ship->courier_name }}</dd></div>
                <div><dt class="text-slate-500">Tracking ID / AWB</dt><dd class="font-mono font-medium">{{ $ship->tracking_number ?? $ship->waybill ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Est. Delivery</dt><dd>{{ $ship->estimated_delivery?->format('M d, Y') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Shipment Status</dt><dd><span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-xs font-semibold">{{ $ship->statusLabel() }}</span></dd></div>
                @if($ship->tracking_url)<div class="sm:col-span-2"><dt class="text-slate-500">Tracking Link</dt><dd><a href="{{ $ship->tracking_url }}" target="_blank" class="text-indigo-600 hover:underline break-all">{{ $ship->tracking_url }}</a></dd></div>@endif
            </dl>
            @if($ship->tracking->isNotEmpty())
            <div class="mb-5 pt-4 border-t"><h4 class="text-sm font-semibold mb-2">Tracking History</h4>
                @foreach($ship->tracking->sortByDesc('scan_time') as $scan)
                <div class="flex justify-between text-xs py-2 border-b border-slate-50"><span>{{ $scan->status }} @if($scan->location)· {{ $scan->location }}@endif</span><span class="text-slate-400">{{ $scan->scan_time?->format('M d H:i') }}</span></div>
                @endforeach
            </div>
            @endif
            @endif

            <form id="shipment-form" class="space-y-3 border-t pt-4">
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Courier Name</label>
                        <input type="text" name="courier_name" value="{{ old('courier_name', $ship->courier_name ?? '') }}" placeholder="e.g. DTDC, India Post" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tracking ID / AWB *</label>
                        <input type="text" name="tracking_number" value="{{ old('tracking_number', $ship->tracking_number ?? $ship->waybill ?? '') }}" required placeholder="Enter tracking number" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tracking URL (optional)</label>
                        <input type="url" name="tracking_url" value="{{ old('tracking_url', $ship->tracking_url ?? '') }}" placeholder="https://..." class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Estimated Delivery</label>
                        <input type="date" name="estimated_delivery" value="{{ old('estimated_delivery', optional($ship?->estimated_delivery)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Remarks (optional)</label>
                        <input type="text" name="remarks" placeholder="Dispatched from warehouse" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <button type="submit" class="px-4 py-2.5 text-sm font-semibold bg-indigo-600 text-white rounded-xl hover:bg-indigo-700">Save Shipment Details</button>
            </form>
        </div>

        {{-- Timeline --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-bold text-lg mb-4">Order Timeline</h3>
            <div class="space-y-0">
                @forelse($timeline as $log)
                <div class="flex gap-4 pb-6 relative">
                    <div class="w-3 h-3 rounded-full bg-indigo-600 mt-1.5 shrink-0"></div>
                    <div>
                        <p class="font-semibold text-slate-900">{{ $log->title }}</p>
                        @if($log->remarks)<p class="text-sm text-slate-500">{{ $log->remarks }}</p>@endif
                        <p class="text-xs text-slate-400 mt-1">{{ $log->created_at->format('M d, Y H:i') }} · {{ $log->actor }}</p>
                    </div>
                </div>
                @empty
                <p class="text-slate-400 text-sm">No timeline events yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border p-6 space-y-3 sticky top-24">
            <h3 class="font-bold">Customer</h3>
            <p class="font-semibold">{{ $o->customer?->full_name ?? $o->user?->name }}</p>
            <p class="text-sm text-slate-500">{{ $o->customer?->mobile ?? $o->user?->phone ?? '—' }}</p>
            <p class="text-sm text-slate-500">{{ $o->customer?->email ?? $o->user?->email }}</p>
            <hr class="my-3">
            <div class="flex justify-between text-sm"><span>Order Status</span><span class="font-semibold capitalize">{{ str_replace('_',' ',$o->status) }}</span></div>
            <div class="flex justify-between text-sm"><span>Payment</span><span class="font-semibold capitalize">{{ $o->payment_status }} ({{ $o->payment_method }})</span></div>
            @if($o->razorpay_payment_id)
            <div class="flex justify-between text-sm"><span>Razorpay ID</span><span class="font-mono text-xs text-slate-500">{{ $o->razorpay_payment_id }}</span></div>
            @endif
            @if($o->payment_method === 'online' && $o->payment_status !== 'paid')
            <button data-action="sync-payment" class="action-btn w-full mt-3 px-4 py-2.5 text-sm font-semibold bg-indigo-50 text-indigo-700 rounded-xl hover:bg-indigo-100">↻ Sync payment from Razorpay</button>
            @endif
        </div>

        <div class="bg-white rounded-2xl border p-6 mb-4">
            <h3 class="font-bold mb-3">Manual Status Update</h3>
            <p class="text-xs text-slate-400 mb-2">Update order status manually.</p>
            <select id="manual-status" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 mb-2">
                @foreach([
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'packing' => 'Packing',
                    'packed' => 'Packed',
                    'shipped' => 'Shipped',
                    'out_for_delivery' => 'Out for Delivery',
                    'delivered' => 'Delivered',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    'returned' => 'Returned',
                    'refunded' => 'Refunded',
                ] as $value => $label)
                <option value="{{ $value }}" @selected($o->status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="text" id="manual-status-remarks" placeholder="Remarks (optional)" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 mb-2">
            <button id="manual-status-btn" class="w-full px-4 py-2.5 text-sm font-semibold bg-slate-800 text-white rounded-xl hover:bg-slate-900">Update Status</button>
        </div>

        <div class="bg-white rounded-2xl border p-6 space-y-2" id="order-actions">
            <h3 class="font-bold mb-3">Actions</h3>
            <button data-action="confirm" class="action-btn w-full text-left px-4 py-2.5 text-sm font-semibold bg-emerald-50 text-emerald-700 rounded-xl hover:bg-emerald-100">✓ Confirm Order</button>
            <button data-action="generate-invoice" class="action-btn w-full text-left px-4 py-2.5 text-sm font-semibold bg-slate-50 text-slate-700 rounded-xl hover:bg-slate-100">🧾 Generate Invoice</button>
            <a href="{{ route('admin.orders.invoice', $o) }}" target="_blank" class="block w-full text-left px-4 py-2.5 text-sm font-semibold bg-slate-50 text-slate-700 rounded-xl hover:bg-slate-100">🖨️ Print Invoice</a>
            <button data-action="generate-label" class="action-btn w-full text-left px-4 py-2.5 text-sm font-semibold bg-slate-50 text-slate-700 rounded-xl hover:bg-slate-100">🏷️ Generate Shipping Label</button>
            @if($ship && ($ship->tracking_number || $ship->waybill))
            <a href="{{ route('admin.orders.label', $o) }}" target="_blank" class="block w-full text-left px-4 py-2.5 text-sm font-semibold bg-slate-50 text-slate-700 rounded-xl hover:bg-slate-100">🖨️ Print Shipping Label</a>
            @endif
            <button data-action="return" class="action-btn w-full text-left px-4 py-2.5 text-sm font-semibold bg-orange-50 text-orange-700 rounded-xl hover:bg-orange-100">↩ Return</button>
            <button data-action="refund" class="action-btn w-full text-left px-4 py-2.5 text-sm font-semibold bg-red-50 text-red-700 rounded-xl hover:bg-red-100">💸 Refund</button>
            <button data-action="cancel" class="action-btn w-full text-left px-4 py-2.5 text-sm font-semibold border border-red-200 text-red-600 rounded-xl hover:bg-red-50">Cancel Order</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
const base = @js(url('/admin/orders/'.$o->id));
const routes = {
    confirm: { method: 'PATCH', url: `${base}/confirm` },
    'sync-payment': { method: 'POST', url: `${base}/sync-payment` },
    'generate-invoice': { method: 'POST', url: `${base}/generate-invoice` },
    'generate-label': { method: 'POST', url: `${base}/generate-label` },
    return: { method: 'POST', url: `${base}/return` },
    refund: { method: 'POST', url: `${base}/refund` },
    cancel: { method: 'POST', url: `${base}/cancel` },
};

document.getElementById('shipment-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const payload = Object.fromEntries(new FormData(form).entries());
    try {
        const res = await fetch(`${base}/save-shipment`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const json = await res.json();
        alert(json.message || (json.success ? 'Saved.' : 'Failed'));
        if (json.success) location.reload();
    } catch (err) { alert(err.message); }
});

const manualBtn = document.getElementById('manual-status-btn');
if (manualBtn) {
    manualBtn.addEventListener('click', async () => {
        const status = document.getElementById('manual-status').value;
        const remarks = document.getElementById('manual-status-remarks').value;
        if (!confirm(`Set order status to "${status.replace(/_/g,' ')}"?`)) return;
        try {
            const res = await fetch(`${base}/update-status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ status, remarks }),
            });
            const json = await res.json();
            alert(json.message || (json.success ? 'Updated.' : 'Failed'));
            if (json.success) location.reload();
        } catch (e) { alert(e.message); }
    });
}

document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const action = btn.dataset.action;
        if (!confirm(`Proceed with: ${action.replace('-',' ')}?`)) return;
        const r = routes[action];
        try {
            const res = await fetch(r.url, { method: r.method, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' } });
            const json = await res.json();
            alert(json.message || (json.success ? 'Done.' : 'Failed'));
            if (json.success) location.reload();
        } catch (e) { alert(e.message); }
    });
});
</script>
@endpush
