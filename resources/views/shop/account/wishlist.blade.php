@extends('layouts.shop')
@section('title', 'Wishlist')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <a href="{{ route('dashboard') }}" class="text-sm text-orange-600 mb-4 inline-block">← Back</a>
    <h1 class="text-2xl font-bold mb-6">My Wishlist</h1>
    @forelse($customer->wishlists as $w)
    <div class="bg-white border rounded-2xl p-4 mb-3 flex justify-between items-center">
        <div><p class="font-semibold">{{ $w->product?->name }}</p><p class="text-sm text-slate-500">Added {{ $w->created_at->format('M d, Y') }}</p></div>
        <a href="{{ route('products.show', $w->product?->slug) }}" class="text-sm bg-orange-600 text-white px-4 py-2 rounded-xl">View Product</a>
    </div>
    @empty<p class="text-slate-400">Your wishlist is empty.</p>@endforelse
</div>
@endsection
