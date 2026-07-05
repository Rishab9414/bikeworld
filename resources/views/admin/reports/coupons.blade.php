@extends('admin.layouts.app')

@section('title', $meta['title'])
@section('page-title', $meta['title'])
@section('page-subtitle', $meta['description'])

@section('content')
@include('admin.reports.partials.filters', ['type' => $type, 'showDateFilter' => true])

@php
    $s = $data['summary'];
    $cards = [
        ['label' => 'Total Coupons', 'value' => $s['total_coupons'], 'format' => 'number'],
        ['label' => 'Active Coupons', 'value' => $s['active_coupons'], 'format' => 'number'],
        ['label' => 'Total Usages', 'value' => $s['total_usages'], 'format' => 'number'],
        ['label' => 'Discount Given', 'value' => $s['total_discount'], 'format' => 'money', 'hint' => $s['orders_with_coupon'].' orders with coupons'],
    ];
@endphp
@include('admin.reports.partials.stat-cards', ['cards' => $cards])

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-lg font-bold text-slate-900">Coupon Performance</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-50 text-left">
                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Code</th>
                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Type</th>
                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Value</th>
                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Usages</th>
                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Discount Given</th>
                <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Order Revenue</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($data['by_coupon'] as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-bold text-indigo-600">{{ $row['code'] }}</td>
                        <td class="px-6 py-3">{{ $row['type'] }}</td>
                        <td class="px-6 py-3">{{ $row['value'] }}</td>
                        <td class="px-6 py-3 font-semibold">{{ number_format($row['usage_count']) }}</td>
                        <td class="px-6 py-3 text-right text-red-600 font-semibold">-@money($row['discount_given'])</td>
                        <td class="px-6 py-3 text-right font-semibold">@money($row['order_revenue'])</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400">No coupon usage in this period</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
