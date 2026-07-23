<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\VehicleBrand;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('products.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('blog.index'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('search.index'), 'priority' => '0.5', 'changefreq' => 'monthly'],
        ]);

        foreach (Category::where('is_active', true)->get() as $category) {
            $urls->push([
                'loc' => route('products.index', ['category' => $category->slug]),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ]);
        }

        foreach (Product::where('is_active', true)->get(['slug', 'updated_at']) as $product) {
            $urls->push([
                'loc' => route('products.show', $product),
                'lastmod' => $product->updated_at?->toAtomString(),
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ]);
        }

        foreach (['privacy-policy', 'terms-and-conditions', 'shipping-policy', 'return-refund-policy', 'cancellation-policy'] as $slug) {
            $urls->push([
                'loc' => route('pages.show', $slug),
                'priority' => '0.3',
                'changefreq' => 'monthly',
            ]);
        }

        BlogPost::published()->get(['slug', 'updated_at'])->each(function ($post) use ($urls) {
            $urls->push([
                'loc' => route('blog.show', $post),
                'lastmod' => $post->updated_at?->toAtomString(),
                'priority' => '0.7',
                'changefreq' => 'monthly',
            ]);
        });

        VehicleBrand::query()
            ->where('status', 'active')
            ->where('show_in_shop', true)
            ->with(['bikeModels' => fn ($q) => $q->where('status', 'active')->where('show_in_shop', true)])
            ->get()
            ->each(function ($brand) use ($urls) {
                $urls->push([
                    'loc' => route('shop-by-bike.brand', $brand),
                    'priority' => '0.6',
                    'changefreq' => 'weekly',
                ]);

                foreach ($brand->bikeModels as $model) {
                    $urls->push([
                        'loc' => route('shop-by-bike.model', [$brand, $model]),
                        'priority' => '0.6',
                        'changefreq' => 'weekly',
                    ]);
                }
            });

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
