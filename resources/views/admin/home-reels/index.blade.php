@extends('admin.layouts.app')
@section('title', 'Homepage Reels')
@section('page-title', 'Homepage Reels')
@section('page-subtitle', 'Short video reels shown after Shop by Category on homepage')

@section('content')
<div class="mb-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <p class="text-sm text-slate-500">{{ $reels->count() }} reel(s) · 3 visible at a time on homepage</p>
    <div class="flex gap-2">
        <a href="{{ route('admin.settings.homepage') }}" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold px-4 py-2.5 rounded-xl">Homepage Settings</a>
        <a href="{{ route('admin.home-reels.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Reel
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Preview</th>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Title</th>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Badge Label</th>
                <th class="px-5 py-3 text-center font-semibold text-slate-600">Order</th>
                <th class="px-5 py-3 text-center font-semibold text-slate-600">Status</th>
                <th class="px-5 py-3 text-right font-semibold text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($reels as $reel)
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-3">
                    @if($reel->thumbnailUrl())
                    <img src="{{ $reel->thumbnailUrl() }}" alt="" class="w-16 h-24 object-cover rounded-lg border border-slate-200">
                    @else
                    <div class="w-16 h-24 rounded-lg bg-slate-900 flex items-center justify-center text-white text-xs">VIDEO</div>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <p class="font-semibold text-slate-900">{{ $reel->title }}</p>
                    @if($reel->category)
                    <p class="text-xs text-indigo-600 mt-0.5">→ {{ $reel->category->name }}</p>
                    @elseif($reel->link_url)
                    <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $reel->link_url }}</p>
                    @endif
                </td>
                <td class="px-5 py-3">
                    @if($reel->label)
                    <span class="inline-block bg-yellow-400 text-black text-xs font-black px-2 py-0.5 rounded">{{ $reel->label }}</span>
                    @else
                    <span class="text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center text-slate-600">{{ $reel->sort_order }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $reel->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $reel->is_active ? 'Active' : 'Hidden' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('admin.home-reels.edit', $reel) }}" class="inline-flex p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form action="{{ route('admin.home-reels.destroy', $reel) }}" method="POST" class="inline" onsubmit="return confirm('Delete this reel?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No reels yet. Upload short videos (MP4/WebM) for the homepage.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
