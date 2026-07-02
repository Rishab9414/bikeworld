@php
    $img = $product->displayImage();
    $price = $product->selling_price ?? $product->price;
    $mrp = $product->mrp ?? $product->compare_price;
    $discount = $mrp && $mrp > $price ? round((($mrp - $price) / $mrp) * 100) : 0;
@endphp
<div class="group bg-white rounded-2xl border border-zinc-100 overflow-hidden hover:shadow-xl hover:shadow-brand-red/5 hover:border-brand-red/20 transition-all duration-300">
    <a href="{{ route('products.show', $product) }}" class="block relative aspect-square bg-zinc-50 overflow-hidden">
        @if($img)
            <img src="{{ asset('storage/'.$img) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-zinc-100 to-zinc-200">
                <svg class="w-16 h-16 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        @endif
        @if($discount > 0)
            <span class="absolute top-3 left-3 bg-brand-red text-white text-xs font-bold px-2.5 py-1 rounded-md">{{ $discount }}% OFF</span>
        @endif
        @if($product->featured)
            <span class="absolute top-3 right-3 bg-brand-black text-white text-xs font-bold px-2.5 py-1 rounded-md">FEATURED</span>
        @endif
    </a>
    <div class="p-4">
        <p class="text-xs text-brand-red font-semibold uppercase tracking-wide mb-1">{{ $product->brand?->name ?? $product->category?->name }}</p>
        <a href="{{ route('products.show', $product) }}" class="block">
            <h3 class="font-semibold text-brand-black text-sm leading-snug line-clamp-2 group-hover:text-brand-red transition-colors">{{ $product->name }}</h3>
        </a>
        <div class="mt-2 flex items-baseline gap-2">
            <span class="text-lg font-bold text-brand-black">@money($price, 0)</span>
            @if($mrp && $mrp > $price)
                <span class="text-sm text-zinc-400 line-through">@money($mrp, 0)</span>
            @endif
        </div>
        @if($product->isInStock())
            <form action="{{ route('cart.store', $product) }}" method="POST" class="mt-3">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="w-full bg-brand-red text-white py-2.5 px-4 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
                    Add to Cart
                </button>
            </form>
        @else
            <p class="mt-3 text-sm text-brand-red font-semibold text-center py-2.5 bg-red-50 rounded-xl">Out of Stock</p>
        @endif
    </div>
</div>
