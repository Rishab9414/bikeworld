@extends('layouts.shop')

@section('title', 'Shop - ' . config('app.name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Shop All Products</h1>

    <div class="flex flex-col lg:flex-row gap-8">
        <aside class="lg:w-64 shrink-0">
            <div class="bg-white rounded-xl border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Categories</h2>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('products.index') }}" class="text-sm {{ !request('category') ? 'text-orange-600 font-medium' : 'text-gray-600 hover:text-orange-600' }}">
                            All Products
                        </a>
                    </li>
                    @foreach($categories as $category)
                        <li>
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="text-sm {{ request('category') === $category->slug ? 'text-orange-600 font-medium' : 'text-gray-600 hover:text-orange-600' }}">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <div class="flex-1">
            <form method="GET" action="{{ route('products.index') }}" class="mb-6">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    <button type="submit" class="bg-orange-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-orange-700">Search</button>
                </div>
            </form>

            @if($products->isEmpty())
                <div class="text-center py-16">
                    <p class="text-gray-500 text-lg">No products found.</p>
                    <a href="{{ route('products.index') }}" class="text-orange-600 font-medium mt-2 inline-block">Clear filters</a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
