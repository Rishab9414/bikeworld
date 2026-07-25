@extends('admin.layouts.app')

@section('title', 'Maintenance Mode')
@section('page-title', 'Maintenance Mode')
@section('page-subtitle', 'Take the storefront offline with a branded maintenance page')

@section('content')
<form action="{{ route('admin.settings.maintenance.update') }}" method="POST" class="max-w-2xl">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="font-bold text-lg text-slate-900 mb-1">Website status</h3>
                <p class="text-sm text-slate-500">When enabled, customers see an animated maintenance page. Admin panel stays accessible.</p>
            </div>
            <span class="shrink-0 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide
                {{ $isDown ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                <span class="w-2 h-2 rounded-full {{ $isDown ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                {{ $isDown ? 'Offline' : 'Live' }}
            </span>
        </div>

        <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-amber-300 has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50/60 transition-colors">
            <input type="hidden" name="enabled" value="0">
            <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $isDown))
                class="mt-1 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
            <div>
                <p class="font-semibold text-slate-900">Enable maintenance mode</p>
                <p class="text-sm text-slate-500 mt-0.5">Storefront, login, checkout &amp; blog go offline. `/admin` remains available.</p>
            </div>
        </label>

        <div>
            <label for="message" class="block text-sm font-semibold text-slate-700 mb-1.5">Customer message</label>
            <textarea id="message" name="message" rows="3" maxlength="500"
                class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                placeholder="We are upgrading BikeWorld for a smoother ride...">{{ old('message', $message) }}</textarea>
            @error('message')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="eta" class="block text-sm font-semibold text-slate-700 mb-1.5">Expected back online <span class="font-normal text-slate-400">(optional)</span></label>
            <input type="datetime-local" id="eta" name="eta"
                value="{{ old('eta', $eta ? \Illuminate\Support\Carbon::parse($eta)->format('Y-m-d\TH:i') : '') }}"
                class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            @error('eta')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-slate-500">Shown as a countdown on the maintenance page when set.</p>
        </div>

        <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-sm text-slate-600 space-y-1">
            <p class="font-medium text-slate-700">Tips</p>
            <ul class="list-disc list-inside space-y-1">
                <li>Preview the page in a private/incognito window after enabling.</li>
                <li>Turn it off here anytime — no SSH required.</li>
                <li>Payments webhooks still work during maintenance.</li>
            </ul>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl">
            Save &amp; Apply
        </button>
        @if($isDown)
            <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-6 py-2.5 rounded-xl">
                Open storefront (will show maintenance)
            </a>
        @endif
    </div>
</form>
@endsection
