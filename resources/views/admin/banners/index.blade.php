@extends('admin.layouts.app')
@section('title', 'Homepage Banners')
@section('page-title', 'Homepage Banners')
@section('page-subtitle', 'Manage full-width slider banners linked to categories')

@section('content')
@if(session('success'))
<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<div class="mb-6 rounded-2xl border border-indigo-200 bg-indigo-50 px-5 py-4">
    <div class="flex flex-wrap items-start gap-4">
        <div class="shrink-0 w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <p class="font-semibold text-indigo-900">Recommended banner size</p>
            <p class="text-sm text-indigo-800 mt-1">
                Upload images at <strong>{{ config('banners.recommended_width') }} × {{ config('banners.recommended_height') }} px</strong>
                (aspect ratio {{ config('banners.aspect_ratio') }}, landscape).
                Minimum {{ config('banners.min_width') }} × {{ config('banners.min_height') }} px.
                Max file size {{ number_format(config('banners.max_file_size_kb') / 1024, 0) }} MB. JPG, PNG, or WebP.
            </p>
            <p class="text-xs text-indigo-700/80 mt-2">Banners display full-width on the homepage slider (up to 860px tall). Use wide landscape images — important text should stay in the left-center area.</p>
        </div>
    </div>
</div>

<div class="mb-4 flex justify-between items-center">
    <p class="text-sm text-slate-500">{{ $banners->count() }} banner(s) · Click order controls sort on storefront</p>
    <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Banner
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Preview</th>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Title</th>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Category</th>
                <th class="px-5 py-3 text-center font-semibold text-slate-600">Size</th>
                <th class="px-5 py-3 text-center font-semibold text-slate-600">Order</th>
                <th class="px-5 py-3 text-center font-semibold text-slate-600">Status</th>
                <th class="px-5 py-3 text-right font-semibold text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($banners as $banner)
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-3">
                    <img src="{{ $banner->imageUrl() }}" alt="" class="w-28 h-16 object-cover rounded-lg border border-slate-200">
                </td>
                <td class="px-5 py-3">
                    <p class="font-semibold text-slate-900">{{ $banner->title }}</p>
                    @if($banner->subtitle)<p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($banner->subtitle, 60) }}</p>@endif
                </td>
                <td class="px-5 py-3 text-slate-600">{{ $banner->category?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-center text-xs whitespace-nowrap {{ $banner->matchesRecommendedSize() ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $banner->imageDimensionsLabel() }}
                </td>
                <td class="px-5 py-3 text-center text-slate-600">{{ $banner->sort_order }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $banner->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $banner->is_active ? 'Active' : 'Hidden' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('admin.banners.edit', $banner) }}" class="inline-flex p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="inline" onsubmit="return confirm('Delete this banner?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No banners yet. Add your first homepage slider banner.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
