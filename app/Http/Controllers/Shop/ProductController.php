<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\SeoService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_active', true);

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('bike_model')) {
            $slug = $request->bike_model;
            $query->whereHas('bikeModels', fn ($q) => $q->where('slug', $slug));
        }

        if ($request->filled('vehicle_brand')) {
            $slug = $request->vehicle_brand;
            $query->whereHas('bikeModels.vehicleBrand', fn ($q) => $q->where('slug', $slug));
        }

        $products = $query->latest()->paginate(12);
        $categories = Category::where('is_active', true)->get();

        $activeCategory = $request->filled('category')
            ? Category::where('slug', $request->category)->first()
            : null;

        $seo = app(SeoService::class)->forProducts($activeCategory, $request->search);

        return view('shop.products.index', compact('products', 'categories', 'seo', 'activeCategory'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'brand']);

        $seo = app(SeoService::class)->forProduct($product);

        $relatedProducts = Product::where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('shop.products.show', compact('product', 'relatedProducts', 'seo'));
    }
}
