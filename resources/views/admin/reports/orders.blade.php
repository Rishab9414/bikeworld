@extends('admin.layouts.app')

@section('title', $meta['title'])
@section('page-title', $meta['title'])
@section('page-subtitle', $meta['description'])

@section('content')
@include('admin.reports.partials.filters', ['type' => $type, 'showDateFilter' => true])

@php
    $s = $data['summary'];
    $cards = [
        ['label' => 'Total Orders', 'value' => $s['total'], 'format' => 'number'],
        ['label' => 'Delivered', 'value' => $s['delivered'], 'format' => 'number'],
        ['label' => 'In Progress', 'value' => $s['in_progress'], 'format' => 'number'],
        ['label' => 'Revenue', 'value' => $s['revenue'], 'format' => 'money', 'hint' => $s['cancelled'].' cancelled'],
    ];
@endphp
@include('admin.reports.partials.stat-cards', ['cards' => $cards])

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Status Funnel</h3>
        @php $maxCount = collect($data['by_status'])->max('count') ?: 1; @endphp
        <div class="space-y-3">
            @foreach($data['by_status'] as $row)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-700">{{ $row['label'] }}</span>
                        <span class="font-semibold">{{ $row['count'] }}</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full" style="width: {{ ($row['count'] / $maxCount) * 100 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">All Orders ({{ count($data['rows']) }})</h3>
        </div>
        <div class="overflow-x-auto max-h-[500px] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-slate-50"><tr class="text-left">
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Order</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Customer</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Payment</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Total</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data['rows'] as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5 font-medium">{{ $row['order_number'] }}</td>
                            <td class="px-4 py-2.5">
                                <div>{{ $row['customer'] }}</div>
                                <div class="text-xs text-slate-400">{{ $row['email'] }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $row['date'] }}</td>
                            <td class="px-4 py-2.5"><span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100">{{ $row['status'] }}</span></td>
                            <td class="px-4 py-2.5 text-xs">{{ $row['payment_method'] }} · {{ $row['payment_status'] }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold">@money($row['total'])</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400">No orders in this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
