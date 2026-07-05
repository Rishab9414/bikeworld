{{-- Stat cards row --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-{{ min(count($cards), 4) }} gap-4 mb-6">
    @foreach($cards as $card)
        <div class="admin-stat-card">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $card['label'] }}</p>
            <p class="text-2xl font-bold text-slate-900 mt-2">
                @if(($card['format'] ?? '') === 'money')
                    @money($card['value'])
                @elseif(($card['format'] ?? '') === 'number')
                    {{ number_format($card['value']) }}
                @else
                    {{ $card['value'] }}
                @endif
            </p>
            @if(!empty($card['hint']))
                <p class="text-xs text-slate-500 mt-2">{{ $card['hint'] }}</p>
            @endif
        </div>
    @endforeach
</div>
