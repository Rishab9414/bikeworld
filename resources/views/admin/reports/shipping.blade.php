@extends('admin.layouts.app')

@section('title', $meta['title'])
@section('page-title', $meta['title'])
@section('page-subtitle', $meta['description'])

@section('content')
@include('admin.reports.partials.filters', ['type' => $type, 'showDateFilter' => true])

@php
    $s = $data['summary'];
    $cards = [
        ['label' => 'Total Shipments', 'value' => $s['total_shipments'], 'format' => 'number'],
        ['label' => 'Delivered', 'value' => $s['delivered'], 'format' => 'number'],
        ['label' => 'In Transit', 'value' => $s['in_transit'], 'format' => 'number'],
        ['label' => 'Shipping Margin', 'value' => $s['margin'], 'format' => 'money', 'hint' => 'Charged ₹'.number_format($s['shipping_charged'], 2).' · Cost ₹'.number_format($s['shipping_cost'], 2)],
    ];
@endphp
@include('admin.reports.partials.stat-cards', ['cards' => $cards])

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100"><h3 class="font-bold text-slate-900">By Status</h3></div>
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-50 text-left">
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Count</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($data['by_status'] as $row)
                    <tr><td class="px-4 py-3">{{ $row['label'] }}</td><td class="px-4 py-3 font-semibold">{{ $row['count'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100"><h3 class="font-bold text-slate-900">By Courier</h3></div>
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-50 text-left">
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Courier</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Shipments</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Cost</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($data['by_courier'] as $row)
                    <tr><td class="px-4 py-3">{{ $row['courier'] }}</td><td class="px-4 py-3">{{ $row['count'] }}</td><td class="px-4 py-3 text-right">@money($row['shipping_cost'])</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100"><h3 class="font-bold text-slate-900">Shipment Details</h3></div>
    <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
        <table class="w-full text-sm">
            <thead class="sticky top-0 bg-slate-50"><tr class="text-left">
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Order</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Waybill</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Courier</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Pickup</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Est. Delivery</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Charged</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($data['rows'] as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2.5 font-medium">{{ $row['order_number'] }}</td>
                        <td class="px-4 py-2.5 font-mono text-xs">{{ $row['waybill'] }}</td>
                        <td class="px-4 py-2.5">{{ $row['courier'] }}</td>
                        <td class="px-4 py-2.5"><span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100">{{ $row['status'] }}</span></td>
                        <td class="px-4 py-2.5 text-slate-600">{{ $row['pickup_date'] }}</td>
                        <td class="px-4 py-2.5 text-slate-600">{{ $row['estimated_delivery'] }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold">@money($row['charged'])</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-10 text-center text-slate-400">No shipments in this period</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
