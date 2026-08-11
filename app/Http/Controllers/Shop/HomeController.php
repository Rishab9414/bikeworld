<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\SeoService;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HomeReel;
use App\Models\Product;
use App\Models\PromoPopup;
use App\Models\Setting;
use App\Models\VehicleBrand;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::with(['category', 'brand'])
            ->where('is_active', true)
            ->where('featured', true)
            ->latest()
            ->take(8)
            ->get();

        $trendingProducts = Product::with(['category', 'brand'])
            ->where('is_active', true)
            ->where('trending', true)
            ->latest()
            ->take(4)
            ->get();

        $newArrivals = Product::with(['category', 'brand'])
            ->where('is_active', true)
            ->where('new_arrival', true)
            ->latest()
            ->take(4)
            ->get();

        $rootCategory = Category::where('slug', 'bike-accessories')->first();

        $categoryQuery = Category::query()
            ->where('is_active', true)
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('display_order');

        $categories = (clone $categoryQuery)
            ->where('parent_id', $rootCategory?->id)
            ->take(8)
            ->get();

        if ($categories->isEmpty()) {
            $categories = $categoryQuery->take(8)->get();
        }

        $brands = Brand::where('status', 'active')->orderBy('name')->take(9)->get();

        $banners = Banner::with('category')->active()->get();

        $shopByBikeEnabled = Setting::shopByBikeEnabled();
        $vehicleBrands = collect();

        if ($shopByBikeEnabled) {
            $vehicleBrands = VehicleBrand::query()
                ->where('status', 'active')
                ->where('show_in_shop', true)
                ->with(['bikeModels' => fn ($q) => $q
                    ->where('status', 'active')
                    ->where('show_in_shop', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        $homeReelsEnabled = Setting::homeReelsEnabled();
        $homeReelsAutoplay = Setting::homeReelsAutoplay();
        $homeReels = collect();

        if ($homeReelsEnabled) {
            $homeReels = HomeReel::with('category')->active()->get();
        }

        $promoPopup = PromoPopup::current();

        $seo = app(SeoService::class)->forHome();

        return view('shop.home', compact(
            'seo',
            'promoPopup',
            'featuredProducts',
            'trendingProducts',
            'newArrivals',
            'categories',
            'brands',
            'banners',
            'shopByBikeEnabled',
            'vehicleBrands',
            'homeReelsEnabled',
            'homeReelsAutoplay',
            'homeReels',
        ));
    }
}
