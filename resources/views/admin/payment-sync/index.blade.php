@extends('admin.layouts.app')
@section('title', 'Sync Payments')
@section('page-title', 'Sync Razorpay Payments')
@section('page-subtitle', 'Check unpaid online orders against Razorpay and update payment status')

@section('content')
@if($mockMode)
<div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
    <strong>Test mode:</strong> Razorpay keys are missing or mock mode is on. Add live keys in <code>.env</code> to sync real payments.
</div>
@endif

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Checked</p>
        <p class="text-3xl font-bold text-slate-900 mt-1">{{ $results['checked'] }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-emerald-200 p-5 shadow-sm">
        <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Updated to Paid</p>
        <p class="text-3xl font-bold text-emerald-700 mt-1">{{ count($results['updated']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-amber-200 p-5 shadow-sm">
        <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Still Pending</p>
        <p class="text-3xl font-bold text-amber-700 mt-1">{{ count($results['still_pending']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-red-200 p-5 shadow-sm">
        <p class="text-xs font-semibold text-red-600 uppercase tracking-wider">Errors</p>
        <p class="text-3xl font-bold text-red-700 mt-1">{{ count($results['errors']) }}</p>
    </div>
</div>

<p class="text-sm text-slate-500 mb-6">Last run: {{ $ranAt }} · Orders from last {{ $days }} days · Max {{ $limit }} orders</p>

<div class="mb-4 flex flex-wrap gap-3">
    <a href="{{ route('admin.orders.sync-payments') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Run Sync Again
    </a>
    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold">← Back to Orders</a>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm mb-6">
    <div class="px-5 py-4 border-b border-slate-100 bg-emerald-50">
        <h3 class="font-bold text-emerald-900">Updated to Paid</h3>
    </div>
    @if(empty($results['updated']))
        <p class="px-5 py-8 text-slate-400 text-sm text-center">No orders were updated this run.</p>
    @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-slate-600">Order</th>
                    <th class="px-5 py-3 text-left font-semibold text-slate-600">Razorpay Payment ID</th>
                    <th class="px-5 py-3 text-left font-semibold text-slate-600">Amount</th>
                    <th class="px-5 py-3 text-left font-semibold text-slate-600">Paid At</th>
                    <th class="px-5 py-3 text-right font-semibold text-slate-600">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($results['updated'] as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-semibold text-slate-900">{{ $row['order_number'] }}</td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $row['razorpay_payment_id'] }}</td>
                    <td class="px-5 py-3">@money($row['amount'])</td>
                    <td class="px-5 py-3 text-slate-600">{{ $row['paid_at'] ?? '—' }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.orders.show', $row['id']) }}" class="text-indigo-600 font-semibold hover:underline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm mb-6">
    <div class="px-5 py-4 border-b border-slate-100 bg-amber-50">
        <h3 class="font-bold text-amber-900">Still Pending (no successful payment on Razorpay)</h3>
    </div>
    @if(empty($results['still_pending']))
        <p class="px-5 py-8 text-slate-400 text-sm text-center">No pending online orders matched, or all checked orders are paid.</p>
    @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-slate-600">Order</th>
                    <th class="px-5 py-3 text-left font-semibold text-slate-600">Status</th>
                    <th class="px-5 py-3 text-left font-semibold text-slate-600">Razorpay Order ID</th>
                    <th class="px-5 py-3 text-left font-semibold text-slate-600">Amount</th>
                    <th class="px-5 py-3 text-right font-semibold text-slate-600">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($results['still_pending'] as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-semibold text-slate-900">{{ $row['order_number'] }}</td>
                    <td class="px-5 py-3 capitalize">{{ $row['payment_status'] }}</td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $row['razorpay_order_id'] }}</td>
                    <td class="px-5 py-3">@money($row['amount'])</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.orders.show', $row['id']) }}" class="text-indigo-600 font-semibold hover:underline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@if(!empty($results['errors']))
<div class="bg-white rounded-2xl border border-red-200 overflow-hidden shadow-sm">
    <div class="px-5 py-4 border-b border-red-100 bg-red-50">
        <h3 class="font-bold text-red-900">Errors</h3>
    </div>
    <table class="w-full text-sm">
        <tbody class="divide-y divide-slate-100">
            @foreach($results['errors'] as $row)
            <tr>
                <td class="px-5 py-3 font-semibold">{{ $row['order_number'] }}</td>
                <td class="px-5 py-3 text-red-600">{{ $row['message'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
