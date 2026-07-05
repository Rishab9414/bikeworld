@extends('layouts.shop')

@section('title', $product->name . ' - ' . config('app.name'))

@php
    $img = $product->displayImage();
    $price = $product->selling_price ?? $product->price;
    $mrp = $product->mrp ?? $product->compare_price;
    $discount = $mrp && $mrp > $price ? round((($mrp - $price) / $mrp) * 100) : 0;
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <nav class="text-sm text-zinc-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-brand-red">Home</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.index') }}" class="hover:text-brand-red">Shop</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-brand-red">{{ $product->category->name }}</a>
        <span class="mx-2">/</span>
        <span class="text-brand-black font-medium">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14">
        <div class="relative aspect-square bg-zinc-50 rounded-2xl overflow-hidden border border-zinc-100">
            @if($img)
                <img src="{{ asset('storage/'.$img) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-zinc-100 to-zinc-200">
                    <svg class="w-32 h-32 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            @endif
            @if($discount > 0)
                <span class="absolute top-4 left-4 bg-brand-red text-white text-xs font-bold px-3 py-1.5 rounded-lg">{{ $discount }}% OFF</span>
            @endif
            <div class="absolute top-4 right-4">
                <x-wishlist-button :product="$product" size="lg" />
            </div>
        </div>

        <div>
            <p class="text-brand-red font-semibold text-sm uppercase tracking-wide mb-2">{{ $product->category->name }}</p>
            <div class="flex items-start justify-between gap-4 mb-4">
                <h1 class="text-3xl font-black text-brand-black tracking-tight">{{ $product->name }}</h1>
            </div>

            <div class="flex items-center gap-3 mb-6">
                <span class="text-3xl font-black text-brand-black">@money($price)</span>
                @if($mrp && $mrp > $price)
                    <span class="text-lg text-zinc-400 line-through">@money($mrp)</span>
                    <span class="bg-red-50 text-brand-red text-sm font-bold px-2.5 py-1 rounded-lg">Save {{ $discount }}%</span>
                @endif
            </div>

            @if($product->description)
            <p class="text-zinc-600 mb-6 leading-relaxed">{{ $product->description }}</p>
            @endif

            <div class="mb-6">
                @if($product->isInStock())
                    <span class="inline-flex items-center text-emerald-700 font-semibold text-sm">
                        <svg class="w-5 h-5 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        In Stock ({{ $product->stock }} available)
                    </span>
                @else
                    <span class="text-brand-red font-semibold">Out of Stock</span>
                @endif
            </div>

            @if($product->isInStock())
                <form action="{{ route('cart.store', $product) }}" method="POST" class="flex items-center gap-3">
                    @csrf
                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-20 rounded-xl border border-zinc-200 px-3 py-3 text-sm focus:border-brand-red focus:ring-2 focus:ring-brand-red/15 outline-none">
                    <button type="submit" class="flex-1 bg-brand-red text-white py-3.5 px-8 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-md shadow-brand-red/20">
                        Add to Cart
                    </button>
                </form>
            @else
                <div class="flex items-center gap-3">
                    <p class="flex-1 text-center text-sm text-brand-red font-semibold py-3.5 bg-red-50 rounded-xl border border-red-100">Out of Stock</p>
                    <x-wishlist-button :product="$product" size="lg" />
                </div>
            @endif
        </div>
    </div>

    @if($relatedProducts->isNotEmpty())
        <section class="mt-16">
            <h2 class="text-2xl font-black text-brand-black mb-6">Related Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $related)
                    <x-product-card :product="$related" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
