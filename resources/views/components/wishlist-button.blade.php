@props(['product', 'size' => 'md'])

@php
    $inWishlist = in_array($product->id, $wishlistProductIds ?? [], true);
    $sizeClasses = match($size) {
        'sm' => 'w-8 h-8',
        'lg' => 'w-11 h-11',
        default => 'w-9 h-9',
    };
    $iconClasses = match($size) {
        'sm' => 'w-4 h-4',
        'lg' => 'w-6 h-6',
        default => 'w-5 h-5',
    };
@endphp

<div
    x-data="{
        wishlisted: {{ $inWishlist ? 'true' : 'false' }},
        loading: false,
        async toggle(e) {
            e.preventDefault();
            e.stopPropagation();
            @guest
            window.location.href = '{{ route('login') }}';
            return;
            @endguest
            if (this.loading) return;
            this.loading = true;
            try {
                const res = await fetch('{{ route('wishlist.toggle', $product) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                if (data.success) {
                    this.wishlisted = data.wishlisted;
                    window.dispatchEvent(new CustomEvent('wishlist-updated', { detail: { count: data.count } }));
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.loading = false;
            }
        }
    }"
    class="relative z-10"
>
    <button
        type="button"
        @click="toggle($event)"
        :disabled="loading"
        :title="wishlisted ? 'Remove from wishlist' : 'Add to wishlist'"
        :aria-label="wishlisted ? 'Remove from wishlist' : 'Add to wishlist'"
        class="{{ $sizeClasses }} rounded-full bg-white/95 backdrop-blur-sm border border-zinc-200/80 shadow-sm flex items-center justify-center transition-all hover:scale-110 hover:border-brand-red/40 disabled:opacity-60"
        :class="wishlisted ? 'text-brand-red border-brand-red/30 bg-red-50/90' : 'text-zinc-500 hover:text-brand-red'"
    >
        <svg x-show="!wishlisted" class="{{ $iconClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
        <svg x-show="wishlisted" x-cloak class="{{ $iconClasses }}" fill="currentColor" viewBox="0 0 24 24">
            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
        </svg>
    </button>
</div>
