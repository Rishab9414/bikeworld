@extends('layouts.shop')
@section('title', 'Loyalty Points')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <a href="{{ route('dashboard') }}" class="text-sm text-orange-600 mb-4 inline-block">← Back</a>
    <h1 class="text-2xl font-bold mb-2">Loyalty Points</h1>
    <p class="text-3xl font-bold text-orange-600">{{ $customer->loyaltyPoint?->total_points ?? 0 }} <span class="text-lg font-normal text-slate-500">points</span></p>
    <p class="text-sm text-slate-500 mb-6">1 Point = ₹{{ config('loyalty.point_value', 1) }} · Min redeem: {{ config('loyalty.min_redeem_points', 100) }} pts</p>
    @forelse($customer->loyaltyPoint?->transactions ?? [] as $tx)
    <div class="flex justify-between py-3 border-b text-sm">
        <span>{{ $tx->remarks ?? ucfirst($tx->transaction_type) }}</span>
        <span class="{{ $tx->points>0?'text-emerald-600':'text-red-600' }} font-semibold">{{ $tx->points>0?'+':'' }}{{ $tx->points }} pts</span>
    </div>
    @empty<p class="text-slate-400 text-sm">No loyalty activity yet.</p>@endforelse
</div>
@endsection
