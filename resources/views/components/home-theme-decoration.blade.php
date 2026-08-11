@props(['homeTheme' => null])

@if($homeTheme && $homeTheme->decoration !== 'none')
<div class="pointer-events-none fixed inset-0 z-[75] overflow-hidden" aria-hidden="true">
    @if($homeTheme->decoration === 'confetti')
        @for($i = 0; $i < 40; $i++)
        <span class="theme-confetti absolute rounded-sm"
            style="left: {{ ($i * 2.5) % 100 }}%; animation-delay: {{ ($i * 0.22) % 8 }}s; animation-duration: {{ 6 + ($i % 5) }}s; background: {{ ['#EC4899','#22D3EE','#FBBF24','#A855F7','#34D399','#FB7185','#38BDF8'][$i % 7] }}; width: {{ 8 + ($i % 5) }}px; height: {{ 12 + ($i % 8) }}px;"></span>
        @endfor
    @elseif($homeTheme->decoration === 'sparkles')
        @for($i = 0; $i < 30; $i++)
        <span class="theme-sparkle absolute"
            style="left: {{ ($i * 3.4) % 100 }}%; animation-delay: {{ ($i * 0.25) % 6 }}s; font-size: {{ 14 + ($i % 12) }}px;">✦</span>
        @endfor
    @elseif($homeTheme->decoration === 'snow')
        @for($i = 0; $i < 35; $i++)
        <span class="theme-snow absolute"
            style="left: {{ ($i * 2.9) % 100 }}%; animation-delay: {{ ($i * 0.3) % 10 }}s; animation-duration: {{ 8 + ($i % 6) }}s; font-size: {{ 12 + ($i % 10) }}px;">❄</span>
        @endfor
    @endif
</div>
@endif
