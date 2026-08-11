@extends('admin.layouts.app')
@section('title', 'Home Themes')
@section('page-title', 'Homepage Themes')
@section('page-subtitle', 'Create festival & seasonal themes — active only within scheduled dates')

@section('content')
@if($liveTheme)
<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-sm font-semibold text-emerald-800">Currently live on storefront</p>
        <p class="text-emerald-700 mt-0.5">{{ $liveTheme->name }} · {{ $liveTheme->presetLabel() }} · {{ $liveTheme->scheduleLabel() }}</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="w-8 h-8 rounded-lg border border-white shadow" style="background: {{ $liveTheme->primary_color }}"></span>
        <span class="w-8 h-8 rounded-lg border border-white shadow" style="background: {{ $liveTheme->secondary_color }}"></span>
        <span class="w-8 h-8 rounded-lg border border-white shadow" style="background: {{ $liveTheme->ticker_bg_color }}"></span>
    </div>
</div>
@else
<div class="mb-5 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
    <p class="text-sm text-slate-600">No theme is live right now. Default brand colors are shown on the homepage.</p>
</div>
@endif

<div class="mb-4 flex justify-between items-center">
    <p class="text-sm text-slate-500">{{ $themes->count() }} theme(s) · Highest priority wins if schedules overlap</p>
    <a href="{{ route('admin.home-themes.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Theme
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Theme</th>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Colors</th>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Schedule</th>
                <th class="px-5 py-3 text-center font-semibold text-slate-600">Priority</th>
                <th class="px-5 py-3 text-center font-semibold text-slate-600">Status</th>
                <th class="px-5 py-3 text-right font-semibold text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($themes as $theme)
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-3">
                    <p class="font-semibold text-slate-900">{{ $theme->name }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $theme->presetLabel() }} · {{ $theme->decorationLabel() }}</p>
                    @if($theme->hero_badge_text)
                    <p class="text-xs text-indigo-500 mt-0.5">Badge: {{ $theme->hero_badge_text }}</p>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-1.5">
                        <span class="w-7 h-7 rounded-md border border-slate-200" style="background: {{ $theme->primary_color }}" title="Primary"></span>
                        <span class="w-7 h-7 rounded-md border border-slate-200" style="background: {{ $theme->secondary_color }}" title="Secondary"></span>
                        <span class="w-7 h-7 rounded-md border border-slate-200" style="background: {{ $theme->ticker_bg_color }}" title="Ticker"></span>
                    </div>
                </td>
                <td class="px-5 py-3 text-slate-600 text-xs">
                    <div>From: {{ $theme->starts_at?->format('d M Y, H:i') ?? 'Anytime' }}</div>
                    <div>Until: {{ $theme->ends_at?->format('d M Y, H:i') ?? 'No end' }}</div>
                </td>
                <td class="px-5 py-3 text-center font-semibold text-slate-700">{{ $theme->priority }}</td>
                <td class="px-5 py-3 text-center">
                    @if($liveTheme && $liveTheme->id === $theme->id)
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Live</span>
                    @elseif($theme->isLive())
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Active</span>
                    @elseif($theme->is_active)
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">Scheduled</span>
                    @else
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">Disabled</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('admin.home-themes.edit', $theme) }}" class="inline-flex p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form action="{{ route('admin.home-themes.destroy', $theme) }}" method="POST" class="inline" onsubmit="return confirm('Delete this theme?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No themes yet. Create a Diwali, Christmas, or sale theme with start/end dates.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6 rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5 text-sm text-slate-700">
    <p class="font-semibold text-indigo-900 mb-2">How it works</p>
    <ul class="space-y-1 list-disc list-inside text-slate-600">
        <li>Pick a preset (Diwali, Holi, Christmas, etc.) — colors auto-fill, you can tweak them.</li>
        <li>Set <strong>start & end date</strong> — theme goes live automatically on those dates.</li>
        <li>Toggle <strong>Enable theme</strong> off to pause without deleting.</li>
        <li>Pair with <a href="{{ route('admin.banners.index') }}" class="text-indigo-600 font-semibold hover:underline">Banners</a>, <a href="{{ route('admin.promo-popups.index') }}" class="text-indigo-600 font-semibold hover:underline">Offer Popup</a>, and <a href="{{ route('admin.announcements.index') }}" class="text-indigo-600 font-semibold hover:underline">Announcements</a> for full festival campaigns.</li>
    </ul>
</div>
@endsection
