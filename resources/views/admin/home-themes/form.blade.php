@extends('admin.layouts.app')
@php
    $isEdit = $theme->exists;
    $presetsJson = json_encode($presets);
@endphp
@section('title', $isEdit ? 'Edit Theme' : 'Add Theme')
@section('page-title', $isEdit ? 'Edit Homepage Theme' : 'Add Homepage Theme')
@section('page-subtitle', 'Design a festival or seasonal look — goes live only within the schedule you set')

@section('content')
<form action="{{ $isEdit ? route('admin.home-themes.update', $theme) : route('admin.home-themes.store') }}" method="POST" class="max-w-3xl"
    x-data="{
        presets: {{ $presetsJson }},
        preset: '{{ old('preset', $theme->preset) }}',
        primary: '{{ old('primary_color', $theme->primary_color) }}',
        secondary: '{{ old('secondary_color', $theme->secondary_color) }}',
        accent: '{{ old('accent_color', $theme->accent_color) }}',
        ticker: '{{ old('ticker_bg_color', $theme->ticker_bg_color) }}',
        decoration: '{{ old('decoration', $theme->decoration) }}',
        heroOverlay: '{{ old('hero_overlay', $theme->hero_overlay) }}',
        applyPreset() {
            const p = this.presets[this.preset];
            if (!p) return;
            this.primary = p.primary_color;
            this.secondary = p.secondary_color;
            this.accent = p.accent_color;
            this.ticker = p.ticker_bg_color;
            this.decoration = p.decoration;
            this.heroOverlay = p.hero_overlay;
        }
    }">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 shadow-sm">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Theme name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $theme->name) }}" required placeholder="Diwali 2026" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Preset <span class="text-red-500">*</span></label>
                <select name="preset" x-model="preset" @change="applyPreset()" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($presets as $key => $preset)
                    <option value="{{ $key }}">{{ $preset['label'] }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">Changing preset updates colors below</p>
                @error('preset')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Priority</label>
                <input type="number" name="priority" value="{{ old('priority', $theme->priority ?? 0) }}" min="0" max="999" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                <p class="text-xs text-slate-400 mt-1">Higher number wins if two themes overlap</p>
                @error('priority')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
            <p class="text-sm font-semibold text-slate-700 mb-3">Color palette</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Primary (buttons, accents)</label>
                    <input type="color" name="primary_color" x-model="primary" class="w-full h-10 rounded-lg border border-slate-200 cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Secondary (dark bg)</label>
                    <input type="color" name="secondary_color" x-model="secondary" class="w-full h-10 rounded-lg border border-slate-200 cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Accent</label>
                    <input type="color" name="accent_color" x-model="accent" class="w-full h-10 rounded-lg border border-slate-200 cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Ticker bar</label>
                    <input type="color" name="ticker_bg_color" x-model="ticker" class="w-full h-10 rounded-lg border border-slate-200 cursor-pointer">
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 rounded-xl overflow-hidden h-12 border border-slate-200">
                <div class="flex-1 h-full" :style="`background: ${secondary}`"></div>
                <div class="w-24 h-full flex items-center justify-center text-xs font-bold text-white" :style="`background: ${primary}`">Shop</div>
                <div class="flex-1 h-full" :style="`background: ${ticker}`"></div>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Homepage decoration</label>
                <select name="decoration" x-model="decoration" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($decorations as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('decoration')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Banner overlay style</label>
                <select name="hero_overlay" x-model="heroOverlay" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($heroOverlays as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('hero_overlay')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Hero badge text <span class="text-slate-400 font-normal">(optional)</span></label>
            <input type="text" name="hero_badge_text" value="{{ old('hero_badge_text', $theme->hero_badge_text) }}" placeholder="Diwali Mega Sale · Up to 40% OFF" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            <p class="text-xs text-slate-400 mt-1">Shown on homepage banner when set (instead of category name)</p>
            @error('hero_badge_text')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Start date/time <span class="text-slate-400 font-normal">(when theme goes live)</span></label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($theme->starts_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                <p class="text-xs text-slate-400 mt-1">Uses store timezone: {{ config('app.timezone') }}. Leave empty to start immediately.</p>
                @error('starts_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">End date/time <span class="text-slate-400 font-normal">(when theme turns off)</span></label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($theme->ends_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                @error('ends_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Internal notes <span class="text-slate-400 font-normal">(optional)</span></label>
            <textarea name="notes" rows="2" placeholder="Use with Diwali banners + popup #3" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $theme->notes) }}</textarea>
            @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $theme->is_active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm font-semibold text-slate-700">Enable theme (must be on + within schedule to show on site)</span>
            </label>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl">{{ $isEdit ? 'Update Theme' : 'Create Theme' }}</button>
        <a href="{{ route('admin.home-themes.index') }}" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold">Cancel</a>
    </div>
</form>
@endsection
