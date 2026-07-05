@extends('admin.layouts.app')

@section('title', $meta['title'])
@section('page-title', $meta['title'])
@section('page-subtitle', $meta['description'])

@section('content')
@include('admin.reports.partials.filters', ['type' => $type, 'showDateFilter' => false])

@php
    $s = $data['summary'];
    $cards = [
        ['label' => 'Total SKUs', 'value' => $s['total_skus'], 'format' => 'number'],
        ['label' => 'Total Units', 'value' => $s['total_units'], 'format' => 'number'],
        ['label' => 'Low Stock', 'value' => $s['low_stock'], 'format' => 'number', 'hint' => $s['out_of_stock'].' out of stock'],
        ['label' => 'Retail Value', 'value' => $s['inventory_retail_value'], 'format' => 'money', 'hint' => 'Cost: ₹'.number_format($s['inventory_cost_value'], 2)],
    ];
@endphp
@include('admin.reports.partials.stat-cards', ['cards' => $cards])

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100"><h3 class="font-bold text-slate-900">Stock by Category</h3></div>
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-50 text-left">
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Category</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Stock</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Low/OOS</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($data['by_category'] as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $row['category'] }}</td>
                        <td class="px-4 py-3">{{ number_format($row['total_stock']) }}</td>
                        <td class="px-4 py-3"><span class="text-amber-600">{{ $row['low_stock'] }}</span> / <span class="text-red-600">{{ $row['out_of_stock'] }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900">Full Inventory</h3>
            <span class="text-xs text-slate-500">{{ count($data['rows']) }} products</span>
        </div>
        <div class="overflow-x-auto max-h-[480px] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-slate-50"><tr class="text-left">
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">SKU</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Product</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Category</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Stock</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Available</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Value</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data['rows'] as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5 font-mono text-xs">{{ $row['sku'] }}</td>
                            <td class="px-4 py-2.5 font-medium">{{ \Illuminate\Support\Str::limit($row['name'], 40) }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $row['category'] }}</td>
                            <td class="px-4 py-2.5 font-semibold">{{ $row['stock'] }}</td>
                            <td class="px-4 py-2.5">{{ $row['available'] }}</td>
                            <td class="px-4 py-2.5">
                                @php
                                    $badge = match($row['status']) {
                                        'Out of stock' => 'bg-red-100 text-red-700',
                                        'Low stock' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-emerald-100 text-emerald-700',
                                    };
                                @endphp
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $badge }}">{{ $row['status'] }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-right">@money($row['retail_value'])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
