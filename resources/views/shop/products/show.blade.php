@extends('layouts.shop')

@section('title', $product->name . ' - ' . config('app.name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <nav class="text-sm text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-orange-600">Home</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.index') }}" class="hover:text-orange-600">Shop</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-orange-600">{{ $product->category->name }}</a>
        <span class="mx-2">/</span>
        <span class="text-gray-900">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <div class="aspect-square bg-gray-100 rounded-xl flex items-center justify-center">
            <svg class="w-32 h-32 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>

        <div>
            <p class="text-orange-600 font-medium mb-2">{{ $product->category->name }}</p>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>

            <div class="flex items-center gap-3 mb-6">
                <span class="text-3xl font-bold text-gray-900">@money($product->price)</span>
                @if($product->compare_price)
                    <span class="text-lg text-gray-400 line-through">@money($product->compare_price)</span>
                    <span class="bg-red-100 text-red-700 text-sm font-medium px-2 py-1 rounded">
                        Save {{ round((1 - $product->price / $product->compare_price) * 100) }}%
                    </span>
                @endif
            </div>

            <p class="text-gray-600 mb-6 leading-relaxed">{{ $product->description }}</p>

            <div class="mb-6">
                @if($product->isInStock())
                    <span class="inline-flex items-center text-green-700 font-medium">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        In Stock ({{ $product->stock }} available)
                    </span>
                @else
                    <span class="text-red-600 font-medium">Out of Stock</span>
                @endif
            </div>

            @if($product->isInStock())
                <form action="{{ route('cart.store', $product) }}" method="POST" class="flex items-center gap-4">
                    @csrf
                    <div>
                        <label for="quantity" class="sr-only">Quantity</label>
                        <input type="number" name="quantity" id="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-20 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    </div>
                    <button type="submit" class="flex-1 bg-orange-600 text-white py-3 px-8 rounded-lg font-semibold hover:bg-orange-700 transition-colors">
                        Add to Cart
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($relatedProducts->isNotEmpty())
        <section class="mt-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $related)
                    <x-product-card :product="$related" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
