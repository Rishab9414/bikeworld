@extends('admin.layouts.app')

@section('title', $meta['title'])
@section('page-title', $meta['title'])
@section('page-subtitle', $meta['description'])

@section('content')
@include('admin.reports.partials.filters', ['type' => $type, 'showDateFilter' => true])

@php
    $s = $data['summary'];
    $cards = [
        ['label' => 'Total Collected', 'value' => $s['total_collected'], 'format' => 'money'],
        ['label' => 'Pending Collection', 'value' => $s['pending_collection'], 'format' => 'money'],
        ['label' => 'Razorpay Collected', 'value' => $s['razorpay_collected'], 'format' => 'money', 'hint' => $s['razorpay_orders'].' online orders'],
        ['label' => 'COD Pending', 'value' => $s['cod_pending'], 'format' => 'number', 'hint' => $s['cod_orders'].' COD orders'],
    ];
@endphp
@include('admin.reports.partials.stat-cards', ['cards' => $cards])

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100"><h3 class="font-bold text-slate-900">By Payment Status</h3></div>
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-50 text-left">
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Orders</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Amount</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($data['by_status'] as $row)
                    <tr><td class="px-4 py-3 capitalize">{{ $row['label'] }}</td><td class="px-4 py-3">{{ $row['count'] }}</td><td class="px-4 py-3 text-right font-semibold">@money($row['amount'])</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100"><h3 class="font-bold text-slate-900">By Payment Method</h3></div>
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-50 text-left">
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Method</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Paid</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Pending</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Amount</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($data['by_method'] as $row)
                    <tr><td class="px-4 py-3">{{ $row['label'] }}</td><td class="px-4 py-3 text-emerald-600">{{ $row['paid'] }}</td><td class="px-4 py-3 text-amber-600">{{ $row['pending'] }}</td><td class="px-4 py-3 text-right font-semibold">@money($row['amount'])</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100"><h3 class="font-bold text-slate-900">Payment Transactions</h3></div>
    <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
        <table class="w-full text-sm">
            <thead class="sticky top-0 bg-slate-50"><tr class="text-left">
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Order</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Date</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Method</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Razorpay ID</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Amount</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($data['rows'] as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2.5 font-medium">{{ $row['order_number'] }}</td>
                        <td class="px-4 py-2.5 text-slate-600">{{ $row['date'] }}</td>
                        <td class="px-4 py-2.5">{{ $row['method'] }}</td>
                        <td class="px-4 py-2.5"><span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $row['status'] === 'Paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $row['status'] }}</span></td>
                        <td class="px-4 py-2.5 text-xs font-mono text-slate-500">{{ $row['razorpay_id'] }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold">@money($row['amount'])</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400">No payments in this period</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
