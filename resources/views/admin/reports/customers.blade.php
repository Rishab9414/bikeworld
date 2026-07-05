@extends('admin.layouts.app')

@section('title', $meta['title'])
@section('page-title', $meta['title'])
@section('page-subtitle', $meta['description'])

@section('content')
@include('admin.reports.partials.filters', ['type' => $type, 'showDateFilter' => true])

@php
    $s = $data['summary'];
    $cards = [
        ['label' => 'Total Customers', 'value' => $s['total_customers'], 'format' => 'number'],
        ['label' => 'New Customers', 'value' => $s['new_customers'], 'format' => 'number'],
        ['label' => 'Repeat Buyers', 'value' => $s['repeat_buyers'], 'format' => 'number'],
        ['label' => 'One-time Buyers', 'value' => $s['one_time_buyers'], 'format' => 'number', 'hint' => $s['active_customers'].' active accounts'],
    ];
@endphp
@include('admin.reports.partials.stat-cards', ['cards' => $cards])

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4">New Registrations</h3>
        @php $maxReg = collect($data['registrations_by_day'])->max('count') ?: 1; @endphp
        @if(collect($data['registrations_by_day'])->isEmpty())
            <p class="text-sm text-slate-400 py-8 text-center">No new registrations</p>
        @else
            <div class="flex items-end gap-1 h-36 overflow-x-auto">
                @foreach($data['registrations_by_day'] as $day)
                    <div class="flex flex-col items-center gap-1 min-w-[28px] h-full justify-end">
                        <div class="w-full bg-sky-500 rounded-t" style="height: {{ max(6, ($day['count'] / $maxReg) * 100) }}px"></div>
                        <span class="text-[9px] text-slate-400">{{ \Carbon\Carbon::parse($day['date'])->format('d') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Top Customers by Spend</h3>
        </div>
        <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-slate-50"><tr class="text-left">
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">#</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Customer</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Mobile</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Orders</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Total Spend</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data['top_customers'] as $i => $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5 text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-2.5">
                                <div class="font-medium">{{ $row['name'] }}</div>
                                <div class="text-xs text-slate-400">{{ $row['code'] }} · {{ $row['email'] }}</div>
                            </td>
                            <td class="px-4 py-2.5">{{ $row['mobile'] }}</td>
                            <td class="px-4 py-2.5">{{ $row['orders'] }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-emerald-600">@money($row['total_spend'])</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-slate-400">No customer orders in this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
