@props(['reels', 'autoplay' => true])

@if($reels->isNotEmpty())
@php $reelCount = $reels->count(); @endphp
<section
    class="py-12 lg:py-16 bg-white overflow-hidden"
    x-data="{
        index: 0,
        total: {{ $reelCount }},
        autoplay: {{ $autoplay ? 'true' : 'false' }},
        perView() { return window.innerWidth >= 768 ? 3 : 1; },
        maxIndex() { return Math.max(0, this.total - this.perView()); },
        slideWidth() { return this.$refs.viewport ? this.$refs.viewport.offsetWidth / this.perView() : 0; },
        trackWidth() { return this.total * this.slideWidth(); },
        offset() { return this.index * this.slideWidth(); },
        next() { if (this.index < this.maxIndex()) this.index++; else this.index = 0; this.syncVideos(); },
        prev() { if (this.index > 0) this.index--; else this.index = this.maxIndex(); this.syncVideos(); },
        syncVideos() {
            if (!this.autoplay) return;
            this.$refs.viewport?.querySelectorAll('video').forEach((v, i) => {
                const visible = i >= this.index && i < this.index + this.perView();
                if (visible) { v.play().catch(() => {}); } else { v.pause(); v.currentTime = 0; }
            });
        },
        initVideos() {
            if (!this.autoplay) return;
            this.$nextTick(() => this.syncVideos());
        }
    }"
    x-init="initVideos(); window.addEventListener('resize', () => { index = Math.min(index, maxIndex()); syncVideos(); })"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4 mb-6 lg:mb-8">
            <div>
                <p class="text-brand-red font-bold text-sm uppercase tracking-widest mb-1">Watch & Shop</p>
                <h2 class="text-2xl sm:text-3xl font-black text-brand-black">VIDEO <span class="text-brand-red">REELS</span></h2>
            </div>
            @if($reelCount > 3)
            <div class="hidden sm:flex items-center gap-2 shrink-0">
                <button type="button" @click="prev()" class="w-10 h-10 rounded-full border-2 border-zinc-300 bg-white text-zinc-700 flex items-center justify-center hover:border-brand-black hover:bg-brand-black hover:text-white transition-colors" aria-label="Previous">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" @click="next()" class="w-10 h-10 rounded-full border-2 border-brand-black bg-brand-black text-white flex items-center justify-center hover:bg-brand-red hover:border-brand-red transition-colors" aria-label="Next">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
            @endif
        </div>

        <div class="overflow-hidden rounded-2xl" x-ref="viewport">
            <div class="flex transition-transform duration-500 ease-out will-change-transform"
                 :style="`width: ${trackWidth()}px; transform: translateX(-${offset()}px)`">
                @foreach($reels as $reel)
                @php $tag = $reel->targetUrl() ? 'a' : 'div'; @endphp
                <{{ $tag }}
                    @if($reel->targetUrl()) href="{{ $reel->targetUrl() }}" @endif
                    class="shrink-0 block px-1.5 sm:px-2 box-border group/reel"
                    :style="`width: ${slideWidth()}px`">
                    <div class="relative rounded-2xl overflow-hidden bg-zinc-900 aspect-[3/4] shadow-lg group-hover/reel:shadow-xl transition-shadow">
                        <video
                            class="w-full h-full object-cover reel-video"
                            src="{{ $reel->videoUrl() }}"
                            @if($reel->thumbnailUrl()) poster="{{ $reel->thumbnailUrl() }}" @endif
                            muted
                            loop
                            playsinline
                            @if($autoplay) autoplay @endif
                            preload="metadata"
                        ></video>
                        @if($reel->label)
                        <span class="absolute top-3 left-3 z-10 bg-yellow-400 text-black text-[10px] sm:text-xs font-black px-2.5 py-1 rounded tracking-wide uppercase shadow-sm pointer-events-none">
                            {{ $reel->label }}
                        </span>
                        @endif
                        @if(!$autoplay)
                        <button type="button"
                            class="absolute inset-0 flex items-center justify-center bg-black/20 hover:bg-black/30 transition-colors z-20"
                            @click.prevent.stop="$el.closest('.group\\/reel')?.querySelector('video')?.play()">
                            <span class="w-12 h-12 rounded-full bg-white/90 flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-brand-black ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </span>
                        </button>
                        @endif
                        <div class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-black/60 to-transparent pointer-events-none"></div>
                        <p class="absolute bottom-3 left-3 right-3 text-white text-xs sm:text-sm font-semibold line-clamp-2 z-10 pointer-events-none">{{ $reel->title }}</p>
                    </div>
                </{{ $tag }}>
                @endforeach
            </div>
        </div>

        @if($reelCount > 1)
        <div class="flex sm:hidden justify-center gap-3 mt-5">
            <button type="button" @click="prev()" class="w-10 h-10 rounded-full border border-zinc-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" @click="next()" class="w-10 h-10 rounded-full bg-brand-black text-white flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
        @endif
    </div>
</section>
@endif
