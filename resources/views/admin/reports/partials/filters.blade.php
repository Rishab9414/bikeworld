{{-- Date range filter bar for reports --}}
@php
    $presets = [
        'today' => 'Today',
        '7d' => 'Last 7 days',
        '30d' => 'Last 30 days',
        'month' => 'This month',
        'year' => 'This year',
        'all' => 'All time',
        'custom' => 'Custom',
    ];
    $showDateFilter = $showDateFilter ?? true;
@endphp

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6">
    <form method="GET" action="{{ $filterAction ?? request()->url() }}" class="flex flex-wrap items-end gap-3">
        @if($showDateFilter)
            <div class="flex flex-wrap gap-2">
                @foreach($presets as $key => $label)
                    <button type="submit" name="preset" value="{{ $key }}"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ ($range['preset'] ?? '30d') === $key ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @if(($range['preset'] ?? '') === 'custom' || request('date_from') || request('date_to'))
                <div class="flex items-center gap-2">
                    <input type="date" name="date_from" value="{{ request('date_from', $range['from']?->format('Y-m-d')) }}"
                        class="admin-input text-sm py-1.5">
                    <span class="text-slate-400 text-sm">to</span>
                    <input type="date" name="date_to" value="{{ request('date_to', $range['to']?->format('Y-m-d')) }}"
                        class="admin-input text-sm py-1.5">
                    <input type="hidden" name="preset" value="custom">
                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700">Apply</button>
                </div>
            @endif
        @endif

        <div class="ml-auto flex items-center gap-2">
            @if(isset($type))
                <a href="{{ route('admin.reports.export', $type) }}?{{ http_build_query(request()->only(['preset', 'date_from', 'date_to'])) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export CSV
                </a>
            @endif
            <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-200 transition-colors">
                ← All Reports
            </a>
        </div>
    </form>

    @if($showDateFilter && $range['from'] && $range['to'])
        <p class="text-xs text-slate-500 mt-3">
            Showing data from <strong>{{ $range['from']->format('M d, Y') }}</strong> to <strong>{{ $range['to']->format('M d, Y') }}</strong>
        </p>
    @elseif($showDateFilter && ($range['preset'] ?? '') === 'all')
        <p class="text-xs text-slate-500 mt-3">Showing all-time data</p>
    @endif
</div>
