@extends('admin.layouts.app')

@section('title', $meta['title'])
@section('page-title', $meta['title'])
@section('page-subtitle', $meta['description'])

@section('content')
@include('admin.reports.partials.filters', ['type' => $type, 'showDateFilter' => true])

@php
    $s = $data['summary'];
    $cards = [
        ['label' => 'Total Revenue', 'value' => $s['revenue'], 'format' => 'money'],
        ['label' => 'Total Orders', 'value' => $s['orders'], 'format' => 'number'],
        ['label' => 'Paid Orders', 'value' => $s['paid_orders'], 'format' => 'number'],
        ['label' => 'Avg Order Value', 'value' => $s['average_order_value'], 'format' => 'money'],
    ];
@endphp
@include('admin.reports.partials.stat-cards', ['cards' => $cards])

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Daily Revenue</h3>
        @php $maxRev = collect($data['daily'])->max('revenue') ?: 1; @endphp
        @if(collect($data['daily'])->isEmpty())
            <p class="text-sm text-slate-400 py-10 text-center">No sales in this period</p>
        @else
            <div class="flex items-end gap-1 h-48 overflow-x-auto pb-2">
                @foreach($data['daily'] as $day)
                    <div class="flex flex-col items-center gap-1 min-w-[40px] h-full justify-end">
                        <span class="text-[10px] text-slate-500">{{ $day['orders'] }}</span>
                        <div class="w-full bg-indigo-600 rounded-t hover:bg-indigo-500 transition-colors" style="height: {{ max(8, ($day['revenue'] / $maxRev) * 140) }}px" title="@money($day['revenue'])"></div>
                        <span class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($day['date'])->format('d') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Revenue Breakdown</h3>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-semibold">@money($s['subtotal'])</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Discount</dt><dd class="font-semibold text-red-600">-@money($s['discount'])</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Coupon Discount</dt><dd class="font-semibold text-red-600">-@money($s['coupon_discount'])</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Wallet Used</dt><dd class="font-semibold text-red-600">-@money($s['wallet_discount'])</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Shipping Collected</dt><dd class="font-semibold">@money($s['shipping_collected'])</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Tax Collected</dt><dd class="font-semibold">@money($s['tax_collected'])</dd></div>
            <div class="flex justify-between border-t pt-3"><dt class="font-bold text-slate-900">Net Revenue</dt><dd class="font-bold text-emerald-600">@money($s['revenue'])</dd></div>
        </dl>
        <div class="mt-4 pt-4 border-t text-xs text-slate-500">
            {{ $s['cancelled_orders'] }} cancelled · {{ $s['refunded_orders'] }} refunded/returned
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-lg font-bold text-slate-900">Revenue by Payment Method</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-50 text-left">
                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Method</th>
                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Orders</th>
                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Revenue</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($data['by_payment_method'] as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-medium">{{ $row['label'] }}</td>
                        <td class="px-6 py-3">{{ number_format($row['orders']) }}</td>
                        <td class="px-6 py-3 text-right font-semibold">@money($row['revenue'])</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-6 py-10 text-center text-slate-400">No data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
