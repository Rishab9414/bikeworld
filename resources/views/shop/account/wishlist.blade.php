@extends('layouts.shop')
@section('title', 'Wishlist — ' . config('app.name'))
@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('dashboard') }}" class="text-sm text-brand-red font-semibold mb-4 inline-flex items-center gap-1 hover:text-red-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to account
    </a>
    <h1 class="text-2xl sm:text-3xl font-black text-brand-black mb-2">My Wishlist</h1>
    <p class="text-sm text-zinc-500 mb-8">{{ $customer->wishlists->count() }} saved {{ $customer->wishlists->count() === 1 ? 'item' : 'items' }}</p>

    @if($customer->wishlists->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($customer->wishlists as $w)
            @if($w->product)
            <x-product-card :product="$w->product" />
            @endif
        @endforeach
    </div>
    @else
    <div class="text-center py-16 bg-zinc-50 rounded-2xl border border-zinc-100">
        <svg class="w-16 h-16 text-zinc-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        <p class="text-zinc-500 mb-4">Your wishlist is empty.</p>
        <a href="{{ route('products.index') }}" class="inline-flex bg-brand-red text-white font-bold text-sm px-6 py-3 rounded-xl hover:bg-red-700 transition-colors">Browse products</a>
    </div>
    @endif
</div>
@endsection
