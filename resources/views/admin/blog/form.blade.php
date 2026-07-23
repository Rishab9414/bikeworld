@extends('admin.layouts.app')
@php $isEdit = $post->exists; @endphp
@section('title', $isEdit ? 'Edit Blog Post' : 'New Blog Post')
@section('page-title', $isEdit ? 'Edit Blog Post' : 'New Blog Post')
@section('page-subtitle', 'SEO-friendly articles for riders')

@section('content')
<form action="{{ $isEdit ? route('admin.blog.update', $post) : route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 shadow-sm">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $post->title) }}" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Slug <span class="text-slate-400 font-normal">(auto if empty)</span></label>
            <input type="text" name="slug" value="{{ old('slug', $post->slug) }}" placeholder="how-to-choose-helmet-size" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Excerpt</label>
            <textarea name="excerpt" rows="2" maxlength="500" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Short summary for listing & SEO">{{ old('excerpt', $post->excerpt) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Content <span class="text-red-500">*</span></label>
            <textarea name="content" rows="14" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">{{ old('content', $post->content) }}</textarea>
            @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Featured Image URL</label>
                <input type="text" name="featured_image" value="{{ old('featured_image', $post->featured_image) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Or upload image</label>
                <input type="file" name="featured_image_file" accept="image/*" class="w-full text-sm">
                @if($post->imageUrl())
                    <img src="{{ $post->imageUrl() }}" alt="" class="mt-2 h-20 rounded-lg object-cover">
                @endif
            </div>
        </div>

        <div class="border-t border-slate-100 pt-5">
            <h3 class="font-bold text-slate-800 mb-3">SEO</h3>
            <div class="space-y-3">
                <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" placeholder="Meta title" class="w-full rounded-xl border-slate-200 text-sm">
                <textarea name="meta_description" rows="2" placeholder="Meta description" class="w-full rounded-xl border-slate-200 text-sm">{{ old('meta_description', $post->meta_description) }}</textarea>
                <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}" placeholder="Keywords (comma separated)" class="w-full rounded-xl border-slate-200 text-sm">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="draft" @selected(old('status', $post->status) === 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $post->status) === 'published')>Published</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Publish date</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-indigo-600 text-white font-semibold px-6 py-2.5 rounded-xl hover:bg-indigo-700">{{ $isEdit ? 'Update Post' : 'Create Post' }}</button>
            <a href="{{ route('admin.blog.index') }}" class="border border-slate-200 px-6 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50">Cancel</a>
        </div>
    </div>
</form>
@endsection
