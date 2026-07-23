@extends('admin.layouts.app')
@section('title', 'Blog Posts')
@section('page-title', 'Blog Posts')
@section('page-subtitle', 'SEO articles, riding tips & gear guides')

@section('content')
<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-slate-500">{{ $posts->total() }} posts</p>
    <a href="{{ route('admin.blog.create') }}" class="bg-indigo-600 text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-indigo-700 text-sm">+ New Post</a>
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-emerald-50 text-emerald-800 rounded-xl text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-5 py-3 font-semibold text-slate-600">Title</th>
                <th class="text-left px-5 py-3 font-semibold text-slate-600">Status</th>
                <th class="text-left px-5 py-3 font-semibold text-slate-600">Published</th>
                <th class="text-left px-5 py-3 font-semibold text-slate-600">Views</th>
                <th class="text-right px-5 py-3 font-semibold text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($posts as $post)
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-4">
                    <p class="font-semibold text-slate-900">{{ $post->title }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">/blog/{{ $post->slug }}</p>
                </td>
                <td class="px-5 py-4">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $post->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($post->status) }}</span>
                </td>
                <td class="px-5 py-4 text-slate-600">{{ $post->published_at?->format('M d, Y') ?? '—' }}</td>
                <td class="px-5 py-4 text-slate-600">{{ number_format($post->views) }}</td>
                <td class="px-5 py-4 text-right space-x-2">
                    @if($post->status === 'published')
                    <a href="{{ route('blog.show', $post) }}" target="_blank" class="text-indigo-600 hover:underline text-xs font-semibold">View</a>
                    @endif
                    <a href="{{ route('admin.blog.edit', $post) }}" class="text-slate-600 hover:underline text-xs font-semibold">Edit</a>
                    <form action="{{ route('admin.blog.destroy', $post) }}" method="POST" class="inline" onsubmit="return confirm('Delete this post?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-xs font-semibold">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-12 text-center text-slate-400">No blog posts yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $posts->links() }}</div>
@endsection
