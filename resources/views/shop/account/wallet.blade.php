@extends('layouts.shop')
@section('title', 'Wallet')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <a href="{{ route('dashboard') }}" class="text-sm text-orange-600 mb-4 inline-block">← Back</a>
    <h1 class="text-2xl font-bold mb-2">My Wallet</h1>
    <p class="text-3xl font-bold text-orange-600 mb-6">@money($customer->wallet?->current_balance ?? 0)</p>
    <h2 class="font-semibold mb-3">Transaction History</h2>
    @forelse($customer->wallet?->transactions ?? [] as $tx)
    <div class="flex justify-between py-3 border-b text-sm">
        <span>{{ $tx->description }}</span>
        <span class="{{ $tx->transaction_type==='credit'?'text-emerald-600':'text-red-600' }} font-semibold">{{ $tx->transaction_type==='credit'?'+':'-' }}@money($tx->amount)</span>
    </div>
    @empty<p class="text-slate-400 text-sm">No transactions yet.</p>@endforelse
</div>
@endsection
