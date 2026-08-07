@extends('layouts.shop')

@section('title', $product->name . ' - ' . config('app.name'))

@php
    $img = $product->displayImage();
    $defaultImageUrl = $img ? asset('storage/'.$img) : null;
    $galleryUrls = $product->galleryImageUrls();
    if (empty($galleryUrls) && $defaultImageUrl) {
        $galleryUrls = [$defaultImageUrl];
    }
    $price = $product->selling_price ?? $product->price;
    $mrp = $product->mrp ?? $product->compare_price;
    $discount = $mrp && $mrp > $price ? round((($mrp - $price) / $mrp) * 100) : 0;
    $activeVariants = $product->variants->where('is_active', true)->values();
    $variantOptions = $activeVariants->map(fn ($variant) => [
        'id' => $variant->id,
        'label' => $variant->label(),
        'price' => (float) ($variant->price ?? $price),
        'stock' => (int) $variant->stock,
        'image' => $variant->imageUrl() ?? $defaultImageUrl,
    ])->values();
    $hasVariants = $variantOptions->isNotEmpty();
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="{
        variants: @js($variantOptions),
        hasVariants: @js($hasVariants),
        selectedId: null,
        defaultImage: @js($defaultImageUrl),
        galleryImages: @js($galleryUrls),
        activeImage: @js($galleryUrls[0] ?? $defaultImageUrl),
        basePrice: @js((float) $price),
        baseMrp: @js($mrp ? (float) $mrp : null),
        baseStock: @js((int) $product->stock),
        init() {
            if (!this.hasVariants) return;
            const first = this.variants.find(v => v.stock > 0) || this.variants[0];
            if (first) this.selectedId = first.id;
        },
        get selected() {
            return this.variants.find(v => v.id === this.selectedId) || null;
        },
        get displayImage() {
            if (this.hasVariants && this.selected?.image) {
                return this.selected.image;
            }
            return this.activeImage || this.defaultImage;
        },
        get displayPrice() {
            return this.selected?.price ?? this.basePrice;
        },
        get displayMrp() {
            return this.baseMrp;
        },
        get displayStock() {
            return this.hasVariants ? (this.selected?.stock ?? 0) : this.baseStock;
        },
        get discountPercent() {
            const mrp = this.displayMrp;
            const price = this.displayPrice;
            if (!mrp || mrp <= price) return 0;
            return Math.round(((mrp - price) / mrp) * 100);
        },
        get inStock() {
            return this.displayStock > 0;
        },
        formatMoney(amount) {
            return '₹' + Number(amount).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }
    }">
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
            <template x-if="displayImage">
                <img :src="displayImage" alt="{{ $product->name }}" class="w-full h-full object-cover">
            </template>
            <template x-if="!displayImage">
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-zinc-100 to-zinc-200">
                    <svg class="w-32 h-32 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </template>
            <span x-show="discountPercent > 0" x-cloak class="absolute top-4 left-4 bg-brand-red text-white text-xs font-bold px-3 py-1.5 rounded-lg" x-text="discountPercent + '% OFF'"></span>
            <div class="absolute top-4 right-4">
                <x-wishlist-button :product="$product" size="lg" />
            </div>
        </div>
        <div x-show="!hasVariants && galleryImages.length > 1" x-cloak class="grid grid-cols-5 gap-3 mt-3">
            <template x-for="(image, index) in galleryImages" :key="image + index">
                <button type="button" @click="activeImage = image"
                    :class="activeImage === image ? 'ring-2 ring-brand-red border-brand-red' : 'border-zinc-200 hover:border-brand-red/40'"
                    class="aspect-square rounded-xl overflow-hidden border bg-zinc-50">
                    <img :src="image" alt="{{ $product->name }} thumbnail" class="w-full h-full object-cover">
                </button>
            </template>
        </div>

        <div>
            <p class="text-brand-red font-semibold text-sm uppercase tracking-wide mb-2">{{ $product->category->name }}</p>
            <div class="flex items-start justify-between gap-4 mb-2">
                <h1 class="text-3xl font-black text-brand-black tracking-tight">{{ $product->name }}</h1>
            </div>

            @if($reviewSummary['count'] > 0)
            <div class="flex items-center gap-2 mb-4">
                <x-star-rating :rating="$reviewSummary['average']" size="md" />
                <span class="text-sm text-zinc-600">{{ $reviewSummary['average'] }} · {{ $reviewSummary['count'] }} {{ Str::plural('review', $reviewSummary['count']) }}</span>
            </div>
            @endif

            <div class="flex items-center gap-3 mb-6">
                <span class="text-3xl font-black text-brand-black" x-text="formatMoney(displayPrice)">@money($price)</span>
                <template x-if="displayMrp && displayMrp > displayPrice">
                    <span class="text-lg text-zinc-400 line-through" x-text="formatMoney(displayMrp)"></span>
                </template>
                <span x-show="discountPercent > 0" x-cloak class="bg-red-50 text-brand-red text-sm font-bold px-2.5 py-1 rounded-lg" x-text="'Save ' + discountPercent + '%'"></span>
            </div>

            @if($hasVariants)
            <div class="mb-6">
                <p class="text-sm font-semibold text-brand-black mb-3">Select Variant</p>
                <div class="flex flex-wrap gap-3">
                    <template x-for="variant in variants" :key="variant.id">
                        <button type="button"
                            @click="selectedId = variant.id"
                            :class="selectedId === variant.id ? 'border-brand-red ring-2 ring-brand-red/20' : 'border-zinc-200 hover:border-brand-red/50'"
                            class="rounded-xl border overflow-hidden bg-white transition-all text-left w-24">
                            <div class="aspect-square bg-zinc-50">
                                <template x-if="variant.image">
                                    <img :src="variant.image" :alt="variant.label" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!variant.image">
                                    <div class="w-full h-full flex items-center justify-center text-xs text-zinc-400 px-2 text-center" x-text="variant.label"></div>
                                </template>
                            </div>
                            <div class="px-2 py-2">
                                <p class="text-xs font-semibold text-brand-black line-clamp-2" x-text="variant.label"></p>
                                <p class="text-[11px] mt-0.5" :class="variant.stock > 0 ? 'text-emerald-600' : 'text-brand-red'" x-text="variant.stock > 0 ? 'In stock' : 'Out of stock'"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
            @endif

            @if($product->description)
            <p class="text-zinc-600 mb-6 leading-relaxed">{{ $product->description }}</p>
            @endif

            <div class="mb-6">
                <template x-if="inStock">
                    <span class="inline-flex items-center text-emerald-700 font-semibold text-sm">
                        <svg class="w-5 h-5 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span x-text="'In Stock (' + displayStock + ' available)'"></span>
                    </span>
                </template>
                <template x-if="!inStock">
                    <span class="text-brand-red font-semibold">Out of Stock</span>
                </template>
            </div>

            <template x-if="inStock">
                <form action="{{ route('cart.store', $product) }}" method="POST" class="flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="variant_id" :value="selectedId">
                    <input type="number" name="quantity" value="1" min="1" :max="displayStock" class="w-20 rounded-xl border border-zinc-200 px-3 py-3 text-sm focus:border-brand-red focus:ring-2 focus:ring-brand-red/15 outline-none">
                    <button type="submit" class="flex-1 bg-brand-red text-white py-3.5 px-8 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-md shadow-brand-red/20">
                        Add to Cart
                    </button>
                </form>
            </template>
            <template x-if="!inStock">
                <div class="flex items-center gap-3">
                    <p class="flex-1 text-center text-sm text-brand-red font-semibold py-3.5 bg-red-50 rounded-xl border border-red-100">Out of Stock</p>
                    <x-wishlist-button :product="$product" size="lg" />
                </div>
            </template>
        </div>
    </div>

    {{-- Reviews --}}
    <section class="mt-16 border-t border-zinc-100 pt-12">
        <h2 class="text-2xl font-black text-brand-black mb-6">Customer Reviews</h2>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-800 rounded-xl text-sm border border-emerald-100">{{ session('success') }}</div>
        @endif

        @auth
            @if($canReview)
                <div class="mb-8 max-w-xl">
                    <x-review-form :product="$product" :order-item-id="$reviewOrderItemId" />
                </div>
            @elseif(auth()->check())
                <p class="text-sm text-zinc-500 mb-8">Purchase and receive this product to leave a verified review.</p>
            @endif
        @else
            <p class="text-sm text-zinc-500 mb-8"><a href="{{ route('login') }}" class="text-brand-red font-semibold hover:underline">Sign in</a> to write a review after delivery.</p>
        @endauth

        @forelse($product->approvedReviews as $review)
        <div class="border-b border-zinc-100 py-6 last:border-0">
            <div class="flex flex-wrap items-center gap-3 mb-2">
                <x-star-rating :rating="$review->rating" />
                @if($review->verified_purchase)
                    <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-semibold">Verified Purchase</span>
                @endif
                <span class="text-sm text-zinc-500">{{ $review->customer?->full_name ?? 'Customer' }} · {{ $review->created_at->format('M d, Y') }}</span>
            </div>
            @if($review->title)<p class="font-bold text-brand-black mb-1">{{ $review->title }}</p>@endif
            <p class="text-zinc-600 text-sm leading-relaxed">{{ $review->review }}</p>
        </div>
        @empty
        <p class="text-zinc-500 py-4">No reviews yet. Be the first to review this product!</p>
        @endforelse
    </section>

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
