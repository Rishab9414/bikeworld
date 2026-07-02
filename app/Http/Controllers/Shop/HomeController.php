<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

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

        $categories = Category::where('parent_id', $rootCategory?->id)
            ->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('display_order')
            ->take(8)
            ->get();

        if ($categories->isEmpty()) {
            $categories = Category::where('is_active', true)
                ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                ->take(8)
                ->get();
        }

        $brands = Brand::where('status', 'active')->orderBy('name')->take(9)->get();

        $banners = Banner::with('category')->active()->get();

        return view('shop.home', compact(
            'featuredProducts',
            'trendingProducts',
            'newArrivals',
            'categories',
            'brands',
            'banners',
        ));
    }
}
