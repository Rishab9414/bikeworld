@extends('admin.layouts.app')
@php $isEdit = $banner->exists; @endphp
@section('title', $isEdit ? 'Edit Banner' : 'Add Banner')
@section('page-title', $isEdit ? 'Edit Banner' : 'Add Banner')
@section('page-subtitle', 'Banner click redirects to selected category')

@section('content')
<form action="{{ $isEdit ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="max-w-3xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 shadow-sm">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $banner->title) }}" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Subtitle</label>
            <input type="text" name="subtitle" value="{{ old('subtitle', $banner->subtitle) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Category (click target)</label>
                <select name="category_id" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">— Select category —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $banner->category_id) == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Button Text</label>
                <input type="text" name="button_text" value="{{ old('button_text', $banner->button_text ?? 'Shop Now') }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Custom Link URL <span class="text-slate-400 font-normal">(optional, if no category)</span></label>
            <input type="url" name="link_url" value="{{ old('link_url', $banner->link_url) }}" placeholder="https://..." class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Image URL {{ $isEdit ? '' : '*' }}</label>
            <p class="text-xs text-slate-500 mb-2">Recommended size: <strong>{{ \App\Models\Banner::recommendedSizeLabel() }}</strong> (landscape, {{ config('banners.aspect_ratio') }}). Min {{ config('banners.min_width') }} × {{ config('banners.min_height') }} px.</p>
            <input type="text" name="image" value="{{ old('image', $isEdit ? $banner->image : '') }}" placeholder="https://... or leave blank if uploading file" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            @if($isEdit && $banner->image)
            <img src="{{ $banner->imageUrl() }}" alt="" class="mt-2 w-full max-w-md h-32 object-cover rounded-xl border">
            <p class="text-xs text-slate-500 mt-1">Current size: {{ $banner->imageDimensionsLabel() }} @if($banner->imageDimensionsLabel() !== '—' && ! $banner->matchesRecommendedSize())<span class="text-amber-600">— below recommended minimum</span>@endif</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Or upload image</label>
            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
            <p class="text-xs text-slate-500 mt-1">Use {{ \App\Models\Banner::recommendedSizeLabel() }} for best results on the homepage slider.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" min="0" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-semibold text-slate-700">Active on homepage</span>
                </label>
            </div>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl">{{ $isEdit ? 'Update Banner' : 'Create Banner' }}</button>
        <a href="{{ route('admin.banners.index') }}" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold">Cancel</a>
    </div>
</form>
@endsection
