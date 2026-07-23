@extends('layouts.shop')

@section('title', 'Blog — Riding Tips & Guides')

@section('content')
<div class="bg-zinc-50 min-h-screen py-10 lg:py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl sm:text-4xl font-black text-brand-black mb-3">BikeWorld Blog</h1>
            <p class="text-zinc-600">Riding tips, gear guides & bike care advice for Indian riders.</p>
        </div>

        @if($posts->isEmpty())
            <p class="text-center text-zinc-500 py-16">New articles coming soon. Check back later!</p>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $post)
                <article class="bg-white rounded-2xl border border-zinc-100 overflow-hidden shadow-sm hover:shadow-md transition-shadow group">
                    <a href="{{ route('blog.show', $post) }}" class="block">
                        @if($post->imageUrl())
                            <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="w-full h-48 object-cover group-hover:scale-[1.02] transition-transform duration-300">
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-zinc-100 to-zinc-200 flex items-center justify-center">
                                <span class="text-4xl">🏍️</span>
                            </div>
                        @endif
                        <div class="p-6">
                            <p class="text-xs text-brand-red font-semibold uppercase tracking-wide mb-2">{{ $post->published_at?->format('M d, Y') }} · {{ $post->readingTime() }} min</p>
                            <h2 class="text-lg font-bold text-brand-black group-hover:text-brand-red transition-colors line-clamp-2">{{ $post->title }}</h2>
                            @if($post->excerpt)
                                <p class="text-sm text-zinc-600 mt-2 line-clamp-3">{{ $post->excerpt }}</p>
                            @endif
                            <span class="inline-block mt-4 text-sm font-semibold text-brand-red">Read more →</span>
                        </div>
                    </a>
                </article>
                @endforeach
            </div>
            <div class="mt-10">{{ $posts->links() }}</div>
        @endif
    </div>
</div>
@endsection
