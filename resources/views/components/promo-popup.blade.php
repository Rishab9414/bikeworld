@props(['popup'])

@php
    $storageKey = 'promo_popup_'.$popup->id.'_'.optional($popup->updated_at)->timestamp;
@endphp

<div
    x-data="{
        open: false,
        key: '{{ $storageKey }}',
        cooldownMs: 24 * 60 * 60 * 1000,
        init() {
            try {
                const last = parseInt(localStorage.getItem(this.key) || '0', 10);
                if (last && (Date.now() - last) < this.cooldownMs) return;
            } catch (e) {}
            setTimeout(() => { this.open = true; document.body.style.overflow = 'hidden'; }, 1200);
        },
        close() {
            this.open = false;
            document.body.style.overflow = '';
            try { localStorage.setItem(this.key, Date.now().toString()); } catch (e) {}
        }
    }"
    x-cloak
    x-show="open"
    @keydown.escape.window="close()"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
>
    <div x-show="open" x-transition.opacity.duration.300ms @click="close()" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-md bg-white rounded-2xl overflow-hidden shadow-2xl"
    >
        <button @click="close()" aria-label="Close" class="absolute top-3 right-3 z-10 w-9 h-9 flex items-center justify-center rounded-full bg-white/90 hover:bg-white text-zinc-700 shadow-md transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        @php $target = $popup->targetUrl(); @endphp

        @if($target)
        <a href="{{ $target }}" class="block group">
        @endif
            <div class="relative">
                <img src="{{ $popup->imageUrl() }}" alt="{{ $popup->title }}" class="w-full h-auto object-cover max-h-[60vh]">
            </div>
        @if($target)
        </a>
        @endif

        <div class="p-6 text-center">
            <h2 class="text-xl font-bold text-zinc-900">{{ $popup->title }}</h2>
            @if($popup->subtitle)
            <p class="mt-1.5 text-sm text-zinc-500">{{ $popup->subtitle }}</p>
            @endif

            @if($target)
            <a href="{{ $target }}" class="mt-4 inline-flex items-center justify-center gap-2 bg-brand-red hover:brightness-110 text-white font-semibold px-7 py-3 rounded-full text-sm transition shadow-lg shadow-brand-red/25">
                {{ $popup->button_text ?: 'Shop Now' }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
            @endif

            <button @click="close()" class="mt-3 block w-full text-xs text-zinc-400 hover:text-zinc-600 transition">No thanks, continue browsing</button>
        </div>
    </div>
</div>
