@extends('layouts.shop')

@section('title', $vehicleBrand->name . ' Bikes - ' . config('app.name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <nav class="text-sm text-zinc-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-brand-red">Home</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-800 font-medium">{{ $vehicleBrand->name }}</span>
    </nav>

    <div class="flex items-center gap-4 mb-8">
        @if($vehicleBrand->imageUrl())
        <img src="{{ $vehicleBrand->imageUrl() }}" alt="{{ $vehicleBrand->name }}" class="w-16 h-16 rounded-xl object-cover border border-zinc-200">
        @endif
        <div>
            <p class="text-brand-red font-bold text-sm uppercase tracking-widest mb-1">Shop by Bike</p>
            <h1 class="text-2xl lg:text-3xl font-black text-brand-black uppercase">{{ $vehicleBrand->name }}</h1>
            <p class="text-zinc-500 mt-1">Select your bike model to see compatible products</p>
        </div>
    </div>

    @if($models->isEmpty())
    <div class="text-center py-16 bg-zinc-50 rounded-2xl border border-zinc-100">
        <p class="text-zinc-500 text-lg">No bike models available for this brand yet.</p>
        <a href="{{ route('home') }}" class="text-brand-red font-semibold mt-3 inline-block">← Back to Home</a>
    </div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6">
        @foreach($models as $model)
        <a href="{{ route('shop-by-bike.model', [$vehicleBrand, $model]) }}"
           class="group block">
            <div class="bg-zinc-100 rounded-2xl sm:rounded-3xl p-3 sm:p-4 aspect-[3/4] flex items-end justify-center overflow-hidden group-hover:shadow-lg group-hover:bg-zinc-50 transition-all duration-300">
                @if($model->imageUrl())
                <img src="{{ $model->imageUrl() }}"
                     alt="{{ $model->name }}"
                     class="w-full max-h-[88%] object-contain object-bottom group-hover:scale-105 transition-transform duration-500 drop-shadow-sm">
                @else
                <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400 p-4">
                    <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-xs text-center font-medium">{{ $model->name }}</span>
                </div>
                @endif
            </div>
            <p class="mt-3 text-center text-xs sm:text-sm font-black text-zinc-800 uppercase tracking-wide leading-tight">
                {{ $model->name }}
            </p>
            @if($model->year || $model->engine_cc)
            <p class="text-center text-xs text-zinc-400 mt-0.5">
                {{ collect([$model->year, $model->engine_cc ? $model->engine_cc.' cc' : null])->filter()->implode(' · ') }}
            </p>
            @endif
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
