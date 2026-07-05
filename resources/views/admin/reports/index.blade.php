@extends('admin.layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', 'Business analytics and exportable reports for your store')

@section('content')
@include('admin.reports.partials.filters', ['showDateFilter' => true])

@php
    $cards = [
        ['label' => 'Revenue', 'value' => $overview['revenue'], 'format' => 'money'],
        ['label' => 'Orders', 'value' => $overview['orders'], 'format' => 'number'],
        ['label' => 'Avg Order Value', 'value' => $overview['average_order_value'], 'format' => 'money'],
        ['label' => 'New Customers', 'value' => $overview['new_customers'], 'format' => 'number'],
        ['label' => 'Units Sold', 'value' => $overview['products_sold'], 'format' => 'number'],
    ];
@endphp
@include('admin.reports.partials.stat-cards', ['cards' => $cards])

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($reportTypes as $key => $report)
        <a href="{{ route('admin.reports.show', $key) }}?preset={{ $range['preset'] }}"
            class="group bg-white rounded-2xl border border-slate-100 shadow-sm p-6 hover:border-indigo-200 hover:shadow-md transition-all">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0 group-hover:bg-indigo-600 transition-colors">
                    @include('admin.reports.partials.icon', ['icon' => $report['icon'], 'class' => 'w-6 h-6 text-indigo-600 group-hover:text-white'])
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $report['title'] }}</h3>
                    <p class="text-sm text-slate-500 mt-1">{{ $report['description'] }}</p>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 mt-3">
                        View report
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </div>
        </a>
    @endforeach
</div>
@endsection
