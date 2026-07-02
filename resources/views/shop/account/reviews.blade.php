@extends('layouts.shop')
@section('title', 'My Reviews')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <a href="{{ route('dashboard') }}" class="text-sm text-orange-600 mb-4 inline-block">← Back</a>
    <h1 class="text-2xl font-bold mb-6">My Reviews</h1>
    @forelse($customer->reviews as $r)
    <div class="bg-white border rounded-2xl p-4 mb-3">
        <div class="flex justify-between"><span class="font-semibold">{{ $r->product?->name }}</span><span class="text-orange-500">{{ str_repeat('★', $r->rating) }}</span></div>
        @if($r->title)<p class="font-medium text-sm mt-2">{{ $r->title }}</p>@endif
        <p class="text-sm text-slate-600 mt-1">{{ $r->review }}</p>
        <span class="text-xs text-slate-400 mt-2 inline-block">{{ ucfirst($r->status) }} · {{ $r->created_at->format('M d, Y') }}</span>
    </div>
    @empty<p class="text-slate-400">You haven't written any reviews yet.</p>@endforelse
</div>
@endsection
