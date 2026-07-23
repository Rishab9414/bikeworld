@extends('admin.layouts.app')
@php $isEdit = $popup->exists; @endphp
@section('title', $isEdit ? 'Edit Popup' : 'Add Popup')
@section('page-title', $isEdit ? 'Edit Offer Popup' : 'Add Offer Popup')
@section('page-subtitle', 'Upload a festival/offer image and control when it appears on the homepage')

@section('content')
<form action="{{ $isEdit ? route('admin.promo-popups.update', $popup) : route('admin.promo-popups.store') }}" method="POST" enctype="multipart/form-data" class="max-w-3xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 shadow-sm">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $popup->title) }}" required placeholder="Diwali Mega Sale" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Subtitle</label>
            <input type="text" name="subtitle" value="{{ old('subtitle', $popup->subtitle) }}" placeholder="Flat 30% OFF on all accessories" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            @error('subtitle')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Image URL {{ $isEdit ? '' : '*' }}</label>
            <input type="text" name="image" value="{{ old('image', $isEdit ? $popup->image : '') }}" placeholder="https://... or leave blank if uploading a file" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            @if($isEdit && $popup->image)
            <img src="{{ $popup->imageUrl() }}" alt="" class="mt-2 w-64 h-64 object-cover rounded-xl border">
            @endif
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Or upload image</label>
            <input type="file" name="image_file" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
            <p class="text-xs text-slate-400 mt-1">Recommended a portrait/square image (e.g. 800×800 or 800×1000). Max 5 MB.</p>
            @error('image_file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Button Text</label>
                <input type="text" name="button_text" value="{{ old('button_text', $popup->button_text ?? 'Shop Now') }}" placeholder="Shop Now" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Button Link URL <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="url" name="link_url" value="{{ old('link_url', $popup->link_url) }}" placeholder="https://..." class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                @error('link_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Start date/time <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($popup->starts_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                @error('starts_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">End date/time <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($popup->ends_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                @error('ends_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $popup->is_active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm font-semibold text-slate-700">Enable popup on homepage</span>
            </label>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl">{{ $isEdit ? 'Update Popup' : 'Create Popup' }}</button>
        <a href="{{ route('admin.promo-popups.index') }}" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold">Cancel</a>
    </div>
</form>
@endsection
