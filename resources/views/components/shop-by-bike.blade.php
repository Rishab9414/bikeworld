@props(['brands'])

@if($brands->isNotEmpty())
@php $brandCount = $brands->count(); @endphp
<section
    class="py-12 lg:py-16 bg-white"
    x-data="{
        index: 0,
        total: {{ $brandCount }},
        timer: null,
        perView() {
            const w = window.innerWidth;
            if (w >= 1024) return 5;
            if (w >= 640) return 3;
            return 2;
        },
        maxIndex() {
            return Math.max(0, this.total - this.perView());
        },
        canScroll() {
            return this.maxIndex() > 0;
        },
        slideWidth() {
            return this.$refs.viewport ? this.$refs.viewport.offsetWidth / this.perView() : 0;
        },
        trackWidth() {
            return this.total * this.slideWidth();
        },
        offset() {
            return this.index * this.slideWidth();
        },
        next() {
            if (!this.canScroll()) return;
            this.index = this.index >= this.maxIndex() ? 0 : this.index + 1;
        },
        prev() {
            if (!this.canScroll()) return;
            this.index = this.index <= 0 ? this.maxIndex() : this.index - 1;
        },
        onResize() {
            this.index = Math.min(this.index, this.maxIndex());
        },
        startAutoplay() {
            clearInterval(this.timer);
            if (this.canScroll()) {
                this.timer = setInterval(() => this.next(), 4500);
            }
        },
        stopAutoplay() {
            clearInterval(this.timer);
        }
    }"
    x-init="startAutoplay(); window.addEventListener('resize', () => onResize())"
    @mouseenter="stopAutoplay()"
    @mouseleave="startAutoplay()"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Title + nav arrows (always visible) --}}
        <div class="flex items-center justify-between gap-4 mb-6 lg:mb-8">
            <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-zinc-800 uppercase tracking-tight">
                Shop by Bike
            </h2>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0" x-show="canScroll()" x-cloak>
                <button type="button" @click="prev()" aria-label="Previous brands"
                    class="w-10 h-10 sm:w-11 sm:h-11 lg:w-12 lg:h-12 rounded-full border-2 border-zinc-300 bg-white text-zinc-700 flex items-center justify-center hover:border-zinc-900 hover:bg-zinc-900 hover:text-white transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" @click="next()" aria-label="Next brands"
                    class="w-10 h-10 sm:w-11 sm:h-11 lg:w-12 lg:h-12 rounded-full border-2 border-zinc-900 bg-zinc-900 text-white flex items-center justify-center hover:bg-brand-red hover:border-brand-red transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <div class="overflow-hidden" x-ref="viewport">
            <div
                class="flex transition-transform duration-500 ease-out will-change-transform"
                :style="`width: ${trackWidth()}px; transform: translateX(-${offset()}px)`"
            >
                @foreach($brands as $brand)
                @php
                    $cardImage = $brand->bikeModels->first(fn ($m) => $m->image)?->imageUrl()
                        ?? $brand->imageUrl();
                @endphp
                <a href="{{ route('shop-by-bike.brand', $brand) }}"
                   class="shrink-0 group block pr-4 sm:pr-5 box-border"
                   :style="`width: ${slideWidth()}px`">
                    <div class="bg-zinc-100 rounded-2xl sm:rounded-3xl p-3 sm:p-4 aspect-[3/4] flex items-end justify-center overflow-hidden group-hover:shadow-md group-hover:bg-zinc-50 transition-all duration-300">
                        @if($cardImage)
                        <img src="{{ $cardImage }}"
                             alt="{{ $brand->name }}"
                             class="w-full max-h-[88%] object-contain object-bottom group-hover:scale-105 transition-transform duration-500 drop-shadow-sm">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-14 h-14 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        @endif
                    </div>
                    <p class="mt-3 text-center text-xs sm:text-sm font-black text-zinc-800 uppercase tracking-wide leading-tight px-1">
                        {{ $brand->name }}
                    </p>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
