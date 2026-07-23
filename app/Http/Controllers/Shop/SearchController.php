<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->input('q', ''));

        $products = collect();
        $categories = collect();
        $posts = collect();

        if ($query !== '') {
            $products = Product::query()
                ->with('category')
                ->where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('short_description', 'like', "%{$query}%")
                        ->orWhere('sku', 'like', "%{$query}%");
                })
                ->latest()
                ->paginate(12)
                ->withQueryString();

            $categories = Category::query()
                ->where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->orderBy('name')
                ->limit(6)
                ->get();

            $posts = BlogPost::published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->latest('published_at')
                ->limit(4)
                ->get();
        }

        $seo = app(SeoService::class)->resolve([
            'title' => $query ? "Search: {$query} | ".config('seo.site_name') : 'Search | '.config('seo.site_name'),
            'description' => $query
                ? "Search results for \"{$query}\" — bike accessories, helmets & riding gear at BikeWorld."
                : 'Search bike accessories, helmets, gloves and riding gear at BikeWorld India.',
            'robots' => $query ? 'noindex,follow' : 'index,follow',
            'canonical' => route('search.index'),
        ]);

        return view('shop.search.index', compact('query', 'products', 'categories', 'posts', 'seo'));
    }
}
