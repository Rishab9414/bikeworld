@extends('admin.layouts.app')

@section('title', $meta['title'])
@section('page-title', $meta['title'])
@section('page-subtitle', $meta['description'])

@section('content')
@include('admin.reports.partials.filters', ['type' => $type, 'showDateFilter' => true])

@php
    $s = $data['summary'];
    $cards = [
        ['label' => 'Units Sold', 'value' => $s['units_sold'], 'format' => 'number'],
        ['label' => 'Product Revenue', 'value' => $s['product_revenue'], 'format' => 'money'],
        ['label' => 'Total Products', 'value' => $s['total_products'], 'format' => 'number'],
        ['label' => 'Out of Stock', 'value' => $s['out_of_stock'], 'format' => 'number', 'hint' => $s['active_products'].' active'],
    ];
@endphp
@include('admin.reports.partials.stat-cards', ['cards' => $cards])

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Best Sellers</h3>
        </div>
        <div class="overflow-x-auto max-h-[480px] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-slate-50"><tr class="text-left">
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">#</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Product</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Category</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Qty</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Revenue</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data['best_sellers'] as $i => $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5 text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-2.5">
                                <div class="font-medium">{{ $row['name'] }}</div>
                                <div class="text-xs text-slate-400">{{ $row['sku'] }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $row['category'] }}</td>
                            <td class="px-4 py-2.5 font-semibold">{{ number_format($row['quantity_sold']) }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold">@money($row['revenue'])</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-slate-400">No product sales in this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Revenue by Category</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 text-left">
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Category</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Products</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Qty Sold</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Revenue</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data['by_category'] as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">{{ $row['category'] }}</td>
                            <td class="px-4 py-3">{{ $row['products'] }}</td>
                            <td class="px-4 py-3">{{ number_format($row['quantity_sold']) }}</td>
                            <td class="px-4 py-3 text-right font-semibold">@money($row['revenue'])</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-slate-400">No category data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
