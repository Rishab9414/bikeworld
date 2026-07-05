@extends('admin.layouts.app')

@section('title', 'Homepage Settings')
@section('page-title', 'Homepage Settings')
@section('page-subtitle', 'Control homepage sections for customers')

@section('content')
<form action="{{ route('admin.settings.homepage.update') }}" method="POST" class="max-w-2xl">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
        <div>
            <h3 class="font-bold text-lg text-slate-900 mb-1">Shop by Bike</h3>
            <p class="text-sm text-slate-500 mb-4">Show a brand & model slider on the homepage (below banner). Customers pick their bike to see compatible products.</p>

            <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50">
                <input type="hidden" name="shop_by_bike_enabled" value="0">
                <input type="checkbox" name="shop_by_bike_enabled" value="1" @checked(old('shop_by_bike_enabled', $shopByBikeEnabled))
                    class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="font-semibold text-slate-900">Enable Shop by Bike section</p>
                    <p class="text-sm text-slate-500 mt-0.5">Displays vehicle brands in a slider, then models when a brand is selected.</p>
                </div>
            </label>
        </div>

        <div class="border-t border-slate-100 pt-6">
            <h3 class="font-bold text-lg text-slate-900 mb-1">Video Reels</h3>
            <p class="text-sm text-slate-500 mb-4">Short videos (like reels) shown after Shop by Category. Up to 3 visible at a time with slider arrows when you have more.</p>

            <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50 mb-3">
                <input type="hidden" name="home_reels_enabled" value="0">
                <input type="checkbox" name="home_reels_enabled" value="1" @checked(old('home_reels_enabled', $homeReelsEnabled))
                    class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="font-semibold text-slate-900">Enable Video Reels section</p>
                    <p class="text-sm text-slate-500 mt-0.5">Shows uploaded reel videos on the homepage.</p>
                </div>
            </label>

            <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50">
                <input type="hidden" name="home_reels_autoplay" value="0">
                <input type="checkbox" name="home_reels_autoplay" value="1" @checked(old('home_reels_autoplay', $homeReelsAutoplay))
                    class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="font-semibold text-slate-900">Autoplay videos (muted)</p>
                    <p class="text-sm text-slate-500 mt-0.5">When off, videos show a play button — customer taps to play.</p>
                </div>
            </label>

            <p class="text-sm text-slate-500 mt-3">
                Manage videos in <a href="{{ route('admin.home-reels.index') }}" class="text-indigo-600 hover:underline font-medium">Home Reels</a>.
            </p>
        </div>

        <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-sm text-slate-600 space-y-2">
            <p class="font-medium text-slate-700">Setup steps</p>
            <ol class="list-decimal list-inside space-y-1">
                <li>Add brands in <a href="{{ route('admin.masters.vehicle_brands.index') }}" class="text-indigo-600 hover:underline">Master Data → Vehicle Brands</a></li>
                <li>Add models in <a href="{{ route('admin.masters.bike_models.index') }}" class="text-indigo-600 hover:underline">Master Data → Bike Models</a></li>
                <li>Upload bike images & enable <strong>Show in Shop by Bike</strong> on each brand/model</li>
                <li>Map products to bike models in <a href="{{ route('admin.products.index') }}" class="text-indigo-600 hover:underline">Products → Compatibility</a> tab</li>
            </ol>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl">Save Settings</button>
    </div>
</form>
@endsection
