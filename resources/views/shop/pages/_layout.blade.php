@extends('layouts.shop')

@section('title', ($pageTitle ?? 'Policy') . ' - ' . config('app.name'))

@section('content')
<div class="bg-zinc-50 min-h-screen py-10 lg:py-14">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-zinc-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-brand-red">Home</a>
            <span class="mx-2">/</span>
            <span class="text-brand-black font-medium">{{ $pageTitle }}</span>
        </nav>

        <article class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 sm:p-10">
            <h1 class="text-2xl sm:text-3xl font-black text-brand-black mb-2">{{ $pageTitle }}</h1>
            <p class="text-sm text-zinc-500 mb-8">Last updated: {{ now()->format('d F Y') }}</p>

            <div class="prose-policy space-y-6 text-sm sm:text-base text-zinc-700 leading-relaxed">
                @yield('policy-content')
            </div>
        </article>

        <div class="mt-8 flex flex-wrap gap-3 justify-center text-sm">
            @foreach(\App\Http\Controllers\Shop\PageController::links() as $link)
            <a href="{{ $link['url'] }}" class="px-4 py-2 rounded-full border border-zinc-200 bg-white text-zinc-600 hover:border-brand-red hover:text-brand-red transition-colors {{ ($pageTitle ?? '') === $link['title'] ? 'border-brand-red text-brand-red font-semibold' : '' }}">
                {{ $link['title'] }}
            </a>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .prose-policy h2 { font-size: 1.125rem; font-weight: 700; color: #111; margin-top: 1.5rem; margin-bottom: 0.5rem; }
    .prose-policy h3 { font-size: 1rem; font-weight: 600; color: #111; margin-top: 1rem; margin-bottom: 0.375rem; }
    .prose-policy p { margin-bottom: 0.75rem; }
    .prose-policy ul, .prose-policy ol { margin: 0.5rem 0 1rem 1.25rem; }
    .prose-policy li { margin-bottom: 0.375rem; }
    .prose-policy a { color: #E31E24; text-decoration: underline; }
</style>
@endpush
