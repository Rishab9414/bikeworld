@extends('layouts.shop')

@section('title', $post->title)

@section('content')
<article class="bg-white min-h-screen">
    @if($post->imageUrl())
    <div class="w-full max-h-[420px] overflow-hidden bg-zinc-100">
        <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="w-full h-full object-cover max-h-[420px]">
    </div>
    @endif

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
        <nav class="text-sm text-zinc-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-brand-red">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('blog.index') }}" class="hover:text-brand-red">Blog</a>
            <span class="mx-2">/</span>
            <span class="text-brand-black font-medium line-clamp-1">{{ $post->title }}</span>
        </nav>

        <header class="mb-8">
            <p class="text-sm text-brand-red font-semibold mb-3">{{ $post->published_at?->format('F d, Y') }} · {{ $post->readingTime() }} min read</p>
            <h1 class="text-3xl sm:text-4xl font-black text-brand-black leading-tight">{{ $post->title }}</h1>
            @if($post->excerpt)
                <p class="text-lg text-zinc-600 mt-4 leading-relaxed">{{ $post->excerpt }}</p>
            @endif
        </header>

        <div class="prose-blog text-zinc-700 leading-relaxed space-y-4 text-base">
            {!! nl2br(e($post->content)) !!}
        </div>

        @if($related->isNotEmpty())
        <section class="mt-14 pt-10 border-t border-zinc-100">
            <h2 class="text-xl font-black text-brand-black mb-6">Related Articles</h2>
            <div class="grid sm:grid-cols-3 gap-4">
                @foreach($related as $item)
                <a href="{{ route('blog.show', $item) }}" class="block p-4 rounded-xl border border-zinc-100 hover:border-brand-red/30 transition-colors">
                    <p class="font-semibold text-sm text-brand-black hover:text-brand-red line-clamp-2">{{ $item->title }}</p>
                    <p class="text-xs text-zinc-500 mt-2">{{ $item->published_at?->format('M d, Y') }}</p>
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</article>
@endsection

@push('styles')
<style>
    .prose-blog h2 { font-size: 1.25rem; font-weight: 800; color: #0A0A0A; margin-top: 2rem; margin-bottom: 0.75rem; }
    .prose-blog h3 { font-size: 1.1rem; font-weight: 700; color: #0A0A0A; margin-top: 1.5rem; margin-bottom: 0.5rem; }
    .prose-blog ul, .prose-blog ol { margin: 1rem 0 1rem 1.5rem; }
    .prose-blog li { margin-bottom: 0.5rem; }
</style>
@endpush
