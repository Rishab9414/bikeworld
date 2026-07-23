@props(['rating' => 0, 'size' => 'sm'])

@php
    $sizeClass = match($size) {
        'lg' => 'text-2xl',
        'md' => 'text-lg',
        default => 'text-sm',
    };
@endphp

<span class="inline-flex text-amber-400 {{ $sizeClass }}" aria-label="{{ $rating }} out of 5 stars">
    @for($i = 1; $i <= 5; $i++)
        @if($i <= floor($rating))
            ★
        @elseif($i - 0.5 <= $rating)
            <span class="relative"><span class="text-zinc-300">★</span><span class="absolute inset-0 overflow-hidden w-1/2">★</span></span>
        @else
            <span class="text-zinc-300">★</span>
        @endif
    @endfor
</span>
