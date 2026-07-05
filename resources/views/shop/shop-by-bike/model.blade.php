@extends('layouts.shop')

@section('title', $bikeModel->name . ' Accessories - ' . config('app.name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <nav class="text-sm text-zinc-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-brand-red">Home</a>
        <span class="mx-2">/</span>
        <a href="{{ route('shop-by-bike.brand', $bikeModel->vehicleBrand) }}" class="hover:text-brand-red">{{ $bikeModel->vehicleBrand->name }}</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-800 font-medium">{{ $bikeModel->name }}</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-brand-red font-bold text-sm uppercase tracking-widest mb-1">{{ $bikeModel->vehicleBrand->name }}</p>
            <h1 class="text-2xl lg:text-3xl font-black text-brand-black">{{ $bikeModel->name }}</h1>
            <p class="text-zinc-500 mt-1">Compatible accessories & gear</p>
        </div>
        <a href="{{ route('shop-by-bike.brand', $bikeModel->vehicleBrand) }}" class="text-sm font-semibold text-brand-red hover:underline shrink-0">← All {{ $bikeModel->vehicleBrand->name }} models</a>
    </div>

    @if($products->isEmpty())
    <div class="text-center py-16 bg-zinc-50 rounded-2xl border border-zinc-100">
        <p class="text-zinc-500 text-lg">No products mapped for this bike yet.</p>
        <p class="text-sm text-zinc-400 mt-2">Ask admin to map products in Products → Compatibility tab.</p>
        <a href="{{ route('products.index') }}" class="text-brand-red font-semibold mt-4 inline-block">Browse all products</a>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($products as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
    <div class="mt-8">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
