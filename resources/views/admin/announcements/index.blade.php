@extends('admin.layouts.app')
@section('title', 'Store Announcements')
@section('page-title', 'Store Announcements')
@section('page-subtitle', 'Manage top bar & red scrolling ticker — promos, discounts & trust messages')

@section('content')
<div class="mb-4 flex flex-wrap gap-3 justify-between items-center">
  <div class="text-sm text-slate-500 space-y-1">
    <p><span class="inline-block w-3 h-3 rounded bg-slate-900 mr-1"></span> <strong>Top Bar</strong> — black strip above header (all pages)</p>
    <p><span class="inline-block w-3 h-3 rounded bg-red-600 mr-1"></span> <strong>Ticker</strong> — red scrolling bar on homepage</p>
  </div>
  <a href="{{ route('admin.announcements.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Add Announcement
  </a>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 border-b border-slate-200">
      <tr>
        <th class="px-5 py-3 text-left font-semibold text-slate-600">Message</th>
        <th class="px-5 py-3 text-left font-semibold text-slate-600">Position</th>
        <th class="px-5 py-3 text-left font-semibold text-slate-600">Type</th>
        <th class="px-5 py-3 text-left font-semibold text-slate-600">Schedule</th>
        <th class="px-5 py-3 text-center font-semibold text-slate-600">Order</th>
        <th class="px-5 py-3 text-center font-semibold text-slate-600">Status</th>
        <th class="px-5 py-3 text-right font-semibold text-slate-600">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @forelse($announcements as $item)
      <tr class="hover:bg-slate-50">
        <td class="px-5 py-3">
          <p class="font-medium text-slate-900">{{ $item->label() }}</p>
          @if($item->link_url)<p class="text-xs text-indigo-600 mt-0.5 truncate max-w-xs">{{ $item->link_url }}</p>@endif
        </td>
        <td class="px-5 py-3">
          <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $item->position === 'top_bar' ? 'bg-slate-800 text-white' : 'bg-red-100 text-red-700' }}">
            {{ $item->position === 'top_bar' ? 'Top Bar' : 'Ticker' }}
          </span>
        </td>
        <td class="px-5 py-3 capitalize text-slate-600">{{ $item->type }}</td>
        <td class="px-5 py-3 text-xs text-slate-500">
          @if($item->starts_at || $item->ends_at)
            {{ $item->starts_at?->format('d M Y') ?? '—' }} → {{ $item->ends_at?->format('d M Y') ?? '—' }}
          @else
            Always on
          @endif
        </td>
        <td class="px-5 py-3 text-center text-slate-600">{{ $item->sort_order }}</td>
        <td class="px-5 py-3 text-center">
          @php
            $live = $item->is_active
              && (!$item->starts_at || $item->starts_at <= now())
              && (!$item->ends_at || $item->ends_at >= now());
          @endphp
          <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $live ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
            {{ $live ? 'Live' : ($item->is_active ? 'Scheduled' : 'Hidden') }}
          </span>
        </td>
        <td class="px-5 py-3 text-right space-x-2">
          <a href="{{ route('admin.announcements.edit', $item) }}" class="inline-flex p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </a>
          <form action="{{ route('admin.announcements.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Delete this announcement?')">
            @csrf @method('DELETE')
            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No announcements yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
