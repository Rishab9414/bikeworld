@extends('admin.layouts.app')
@php $isEdit = $reel->exists; @endphp
@section('title', $isEdit ? 'Edit Reel' : 'Add Reel')
@section('page-title', $isEdit ? 'Edit Reel Video' : 'Add Reel Video')
@section('page-subtitle', 'Short vertical-style video for homepage reels section')

@section('content')
<form action="{{ $isEdit ? route('admin.home-reels.update', $reel) : route('admin.home-reels.store') }}" method="POST" enctype="multipart/form-data" class="max-w-3xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 shadow-sm">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $reel->title) }}" required placeholder="e.g. Rynox Jacket Showcase" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Badge Label</label>
            <input type="text" name="label" value="{{ old('label', $reel->label) }}" placeholder="RYNOX, AXOR, TOP BOX..." maxlength="50" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 uppercase">
            <p class="text-xs text-slate-400 mt-1">Yellow badge shown on top-left of video (like brand name).</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Video File {{ $isEdit ? '' : '*' }}</label>
            <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
            <p class="text-xs text-slate-400 mt-1">MP4, WebM or MOV · Max 50MB · Recommended: vertical or square short clip</p>
            @error('video_file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            @if($isEdit && $reel->video)
            <video src="{{ $reel->videoUrl() }}" controls muted class="mt-3 w-full max-w-xs rounded-xl border border-slate-200" style="max-height:240px"></video>
            @endif
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Thumbnail / Poster (optional)</label>
            <input type="file" name="thumbnail_file" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
            @if($isEdit && $reel->thumbnailUrl())
            <img src="{{ $reel->thumbnailUrl() }}" alt="" class="mt-2 w-24 h-36 object-cover rounded-xl border">
            @endif
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Category (click target)</label>
            <select name="category_id" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">— Select category —</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id', $reel->category_id) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-400 mt-1">When selected, clicking the reel opens products in that category.</p>
            @error('category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Custom Link URL <span class="text-slate-400 font-normal">(optional, if no category)</span></label>
            <input type="url" name="link_url" value="{{ old('link_url', $reel->link_url) }}" placeholder="https://..." class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            <p class="text-xs text-slate-400 mt-1">Used only when no category is selected.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $reel->sort_order ?? 0) }}" min="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex items-end pb-2">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $reel->is_active)) class="rounded text-indigo-600">
                    Active on homepage
                </label>
            </div>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl">{{ $isEdit ? 'Update Reel' : 'Create Reel' }}</button>
        <a href="{{ route('admin.home-reels.index') }}" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">Cancel</a>
    </div>
</form>
@endsection
