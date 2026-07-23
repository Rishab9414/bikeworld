@extends('layouts.shop')

@section('title', $query ? "Search: {$query}" : 'Search')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl sm:text-3xl font-black text-brand-black mb-2">
        @if($query)
            Results for "{{ $query }}"
        @else
            Search
        @endif
    </h1>
    <p class="text-zinc-500 text-sm mb-8">Find bike accessories, helmets, gloves, riding gear & more.</p>

    <form action="{{ route('search.index') }}" method="GET" class="mb-10 max-w-xl">
        <div class="flex gap-2">
            <input type="search" name="q" value="{{ $query }}" required placeholder="Search products, categories, articles…"
                class="flex-1 rounded-xl border border-zinc-200 px-4 py-3 text-sm focus:border-brand-red focus:ring-2 focus:ring-brand-red/15 outline-none">
            <button type="submit" class="bg-brand-red text-white font-bold px-6 py-3 rounded-xl hover:bg-red-700 shrink-0">Search</button>
        </div>
    </form>

    @if($query === '')
        <div class="text-center py-16 text-zinc-400">
            <svg class="w-16 h-16 mx-auto mb-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <p>Enter a keyword to search our store.</p>
        </div>
    @else
        @if($categories->isNotEmpty())
        <section class="mb-10">
            <h2 class="text-lg font-bold text-brand-black mb-4">Categories</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                   class="px-4 py-2 rounded-full border border-zinc-200 text-sm font-medium hover:border-brand-red hover:text-brand-red transition-colors">
                    {{ $category->name }}
                </a>
                @endforeach
            </div>
        </section>
        @endif

        @if($posts->isNotEmpty())
        <section class="mb-10">
            <h2 class="text-lg font-bold text-brand-black mb-4">From the Blog</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($posts as $post)
                <a href="{{ route('blog.show', $post) }}" class="group bg-white border border-zinc-100 rounded-2xl p-4 hover:border-brand-red/30 hover:shadow-sm transition-all">
                    <p class="font-semibold text-brand-black group-hover:text-brand-red line-clamp-2">{{ $post->title }}</p>
                    <p class="text-xs text-zinc-500 mt-2">{{ $post->published_at?->format('M d, Y') }} · {{ $post->readingTime() }} min read</p>
                </a>
                @endforeach
            </div>
        </section>
        @endif

        <section>
            <h2 class="text-lg font-bold text-brand-black mb-4">Products</h2>
            @if($products->isEmpty())
                <p class="text-zinc-500 py-8">No products found for "{{ $query }}". Try different keywords.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $products->links() }}</div>
            @endif
        </section>
    @endif
</div>
@endsection
