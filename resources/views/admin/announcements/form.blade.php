@extends('admin.layouts.app')
@php $isEdit = $announcement->exists; @endphp
@section('title', $isEdit ? 'Edit Announcement' : 'Add Announcement')
@section('page-title', $isEdit ? 'Edit Announcement' : 'Add Announcement')
@section('page-subtitle', 'Promo, discount or trust message for top bar or homepage ticker')

@section('content')
<form action="{{ $isEdit ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}" method="POST" class="max-w-3xl">
  @csrf
  @if($isEdit) @method('PUT') @endif

  <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 shadow-sm">
    <div class="grid sm:grid-cols-4 gap-4">
      <div class="sm:col-span-1">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Icon</label>
        <input type="text" name="icon" value="{{ old('icon', $announcement->icon) }}" placeholder="🔥" maxlength="20" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-center text-xl">
        <p class="text-xs text-slate-400 mt-1">Emoji optional</p>
      </div>
      <div class="sm:col-span-3">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Message <span class="text-red-500">*</span></label>
        <input type="text" name="text" value="{{ old('text', $announcement->text) }}" required maxlength="500" placeholder="Flat 20% off helmets — Use code HELMET40" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
        @error('text')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
      </div>
    </div>

    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-1">Link URL <span class="text-slate-400 font-normal">(optional — click opens this page)</span></label>
      <input type="text" name="link_url" value="{{ old('link_url', $announcement->link_url) }}" placeholder="/products?category=helmet or https://..." class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Show on <span class="text-red-500">*</span></label>
        <select name="position" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="ticker" @selected(old('position', $announcement->position) === 'ticker')>Red Ticker (Homepage scroll)</option>
          <option value="top_bar" @selected(old('position', $announcement->position) === 'top_bar')>Top Black Bar (All pages)</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Type <span class="text-red-500">*</span></label>
        <select name="type" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
          <option value="promo" @selected(old('type', $announcement->type) === 'promo')>Promo / Discount</option>
          <option value="trust" @selected(old('type', $announcement->type) === 'trust')>Trust / Service</option>
          <option value="info" @selected(old('type', $announcement->type) === 'info')>Info</option>
        </select>
      </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Start Date <span class="text-slate-400 font-normal">(optional)</span></label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $announcement->starts_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">End Date <span class="text-slate-400 font-normal">(optional)</span></label>
        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $announcement->ends_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
        <p class="text-xs text-slate-400 mt-1">Auto-hide after end date — great for limited offers</p>
      </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $announcement->sort_order ?? 0) }}" min="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $announcement->is_active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
          <span class="text-sm font-semibold text-slate-700">Active</span>
        </label>
      </div>
    </div>
  </div>

  <div class="mt-6 flex gap-3">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl">{{ $isEdit ? 'Update' : 'Create' }}</button>
    <a href="{{ route('admin.announcements.index') }}" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold">Cancel</a>
  </div>
</form>
@endsection
