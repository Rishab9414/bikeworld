@extends('admin.layouts.app')
@section('title', 'Offer Popups')
@section('page-title', 'Homepage Offer Popup')
@section('page-subtitle', 'Festival / offer image popup shown on the homepage')

@section('content')
<div class="mb-4 flex justify-between items-center">
    <p class="text-sm text-slate-500">{{ $popups->count() }} popup(s) · The most recently updated active popup (within its schedule) is shown</p>
    <a href="{{ route('admin.promo-popups.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Popup
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Preview</th>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Title</th>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Schedule</th>
                <th class="px-5 py-3 text-center font-semibold text-slate-600">Status</th>
                <th class="px-5 py-3 text-right font-semibold text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($popups as $popup)
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-3">
                    <img src="{{ $popup->imageUrl() }}" alt="" class="w-24 h-24 object-cover rounded-lg border border-slate-200">
                </td>
                <td class="px-5 py-3">
                    <p class="font-semibold text-slate-900">{{ $popup->title }}</p>
                    @if($popup->subtitle)<p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($popup->subtitle, 60) }}</p>@endif
                    @if($popup->link_url)<p class="text-xs text-indigo-500 mt-0.5 truncate max-w-xs">{{ $popup->link_url }}</p>@endif
                </td>
                <td class="px-5 py-3 text-slate-600 text-xs">
                    <div>From: {{ $popup->starts_at?->format('d M Y, H:i') ?? 'Always' }}</div>
                    <div>Until: {{ $popup->ends_at?->format('d M Y, H:i') ?? 'No end' }}</div>
                </td>
                <td class="px-5 py-3 text-center">
                    @if($popup->isLive())
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Live</span>
                    @elseif($popup->is_active)
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">Scheduled</span>
                    @else
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">Disabled</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('admin.promo-popups.edit', $popup) }}" class="inline-flex p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form action="{{ route('admin.promo-popups.destroy', $popup) }}" method="POST" class="inline" onsubmit="return confirm('Delete this popup?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">No popups yet. Add a festival or offer popup for your homepage.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
