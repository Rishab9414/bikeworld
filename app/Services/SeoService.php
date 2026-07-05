<?php

namespace App\Services;

use App\Models\BikeModel;
use App\Models\Category;
use App\Models\Product;
use App\Models\VehicleBrand;
use Illuminate\Support\Str;

class SeoService
{
    public function defaults(): array
    {
        return [
            'title' => config('seo.default_title'),
            'description' => config('seo.default_description'),
            'keywords' => config('seo.default_keywords'),
            'canonical' => url()->current(),
            'image' => $this->absoluteUrl(config('seo.og_default_image')),
            'type' => 'website',
            'robots' => config('seo.robots', 'index,follow'),
            'site_name' => config('seo.site_name'),
            'locale' => config('seo.locale'),
            'json_ld' => [$this->organizationSchema()],
        ];
    }

    public function resolve(?array $overrides = null, ?string $pageTitle = null): array
    {
        $meta = array_merge($this->defaults(), array_filter($overrides ?? [], fn ($v) => $v !== null && $v !== ''));

        if ($pageTitle && blank($meta['title'])) {
            $meta['title'] = $pageTitle;
        }

        if ($this->shouldNoindex()) {
            $meta['robots'] = 'noindex,nofollow';
        }

        $meta['title'] = $this->truncate($meta['title'], 60);
        $meta['description'] = $this->truncate(strip_tags($meta['description']), 160);

        return $meta;
    }

    public function forHome(): array
    {
        $page = config('seo.pages.home');

        return $this->resolve([
            'title' => $page['title'],
            'description' => $page['description'],
            'keywords' => $page['keywords'],
            'canonical' => route('home'),
            'type' => 'website',
            'json_ld' => [
                $this->organizationSchema(),
                $this->websiteSchema(),
            ],
        ]);
    }

    public function forProducts(?Category $category = null, ?string $search = null): array
    {
        if ($search) {
            return $this->resolve([
                'title' => "Search: {$search} — Bike Accessories | ".config('seo.site_name'),
                'description' => "Results for \"{$search}\" — helmets, gloves, riding gear & bike accessories at BikeWorld India.",
                'keywords' => "{$search}, bike accessories search, motorcycle gear india, ".config('seo.site_name'),
                'canonical' => url()->full(),
                'robots' => 'noindex,follow',
            ]);
        }

        if ($category) {
            return $this->forCategory($category);
        }

        $page = config('seo.pages.products');

        return $this->resolve([
            'title' => $page['title'],
            'description' => $page['description'],
            'keywords' => $page['keywords'],
            'canonical' => route('products.index'),
        ]);
    }

    public function forCategory(Category $category): array
    {
        $site = config('seo.site_name');

        $title = $category->seo_title
            ?: $this->template('category_title', ['name' => $category->name, 'site' => $site]);

        $description = $category->meta_description
            ?: ($category->description
                ? $this->truncate(strip_tags($category->description), 160)
                : $this->template('category_description', [
                    'name' => $category->name,
                    'name_lower' => Str::lower($category->name),
                ]));

        $keywords = $category->seo_keywords
            ?: $this->template('category_keywords', [
                'name' => $category->name,
                'name_lower' => Str::lower($category->name),
            ]);

        return $this->resolve([
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => route('products.index', ['category' => $category->slug]),
            'image' => $category->image ? $this->absoluteUrl('storage/'.$category->image) : null,
        ]);
    }

    public function forProduct(Product $product): array
    {
        $site = config('seo.site_name');
        $categoryName = $product->category?->name ?? 'Bike Accessories';
        $brandName = $product->brand?->name ?? '';
        $price = number_format((float) ($product->selling_price ?? $product->price), 0);
        $stock = $product->isInStock() ? 'In stock.' : 'Check availability.';

        $title = $product->meta_title
            ?: $this->template('product_title', ['name' => $product->name, 'site' => $site]);

        $description = $product->meta_description
            ?: $this->template('product_description', [
                'name' => $product->name,
                'category' => $categoryName,
                'price' => $price,
                'stock' => $stock,
            ]);

        if (! $product->meta_description && $product->short_description) {
            $description = $this->truncate(strip_tags($product->short_description), 160);
        } elseif (! $product->meta_description && $product->description) {
            $description = $this->truncate(strip_tags($product->description), 160);
        }

        $keywords = $product->meta_keywords
            ?: $this->template('product_keywords', [
                'name' => $product->name,
                'category' => $categoryName,
                'brand' => $brandName ?: 'motorcycle',
            ]);

        $imagePath = $product->og_image ?: $product->displayImage();
        $image = $imagePath ? $this->absoluteUrl('storage/'.$imagePath) : null;

        return $this->resolve([
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $product->canonical_url ?: route('products.show', $product),
            'image' => $image,
            'type' => 'product',
            'json_ld' => [
                $this->organizationSchema(),
                $this->productSchema($product, $image),
            ],
        ]);
    }

    public function forVehicleBrand(VehicleBrand $brand): array
    {
        return $this->resolve([
            'title' => $this->template('vehicle_brand_title', ['name' => $brand->name]),
            'description' => $this->template('vehicle_brand_description', ['name' => $brand->name]),
            'keywords' => $this->template('vehicle_brand_keywords', ['name' => $brand->name]),
            'canonical' => route('shop-by-bike.brand', $brand),
            'image' => ($brand->logo ?? $brand->image) ? $this->absoluteUrl('storage/'.($brand->logo ?? $brand->image)) : null,
        ]);
    }

    public function forBikeModel(BikeModel $model): array
    {
        $brandName = $model->vehicleBrand?->name ?? '';

        return $this->resolve([
            'title' => $this->template('bike_model_title', ['brand' => $brandName, 'name' => $model->name]),
            'description' => $this->template('bike_model_description', ['brand' => $brandName, 'name' => $model->name]),
            'keywords' => $this->template('bike_model_keywords', ['brand' => $brandName, 'name' => $model->name]),
            'canonical' => route('shop-by-bike.model', [$model->vehicleBrand, $model]),
            'image' => $model->image ? $this->absoluteUrl('storage/'.$model->image) : null,
        ]);
    }

    public function forPage(string $slug): array
    {
        $page = config("seo.pages.{$slug}", []);

        return $this->resolve([
            'title' => $page['title'] ?? ucwords(str_replace('-', ' ', $slug)).' | '.config('seo.site_name'),
            'description' => $page['description'] ?? config('seo.default_description'),
            'keywords' => $page['keywords'] ?? config('seo.default_keywords'),
            'canonical' => route('pages.show', $slug),
            'type' => 'article',
        ]);
    }

    private function shouldNoindex(): bool
    {
        return request()->routeIs(config('seo.noindex_routes', []));
    }

    private function template(string $key, array $vars): string
    {
        $template = config("seo.templates.{$key}", '');

        foreach ($vars as $name => $value) {
            $template = str_replace('{'.$name.'}', (string) $value, $template);
        }

        return $template;
    }

    private function truncate(string $text, int $limit): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $limit - 1)).'…';
    }

    private function absoluteUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url($path);
    }

    private function organizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('seo.site_name'),
            'url' => config('app.url'),
            'logo' => $this->absoluteUrl(config('seo.og_default_image')),
            'email' => config('store.support_email'),
            'telephone' => config('store.support_phone'),
            'sameAs' => array_values(array_filter([
                config('seo.twitter_handle') ? 'https://twitter.com/'.ltrim(config('seo.twitter_handle'), '@') : null,
            ])),
        ];
    }

    private function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('seo.site_name'),
            'url' => config('app.url'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('products.index').'?search={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    private function productSchema(Product $product, ?string $image): array
    {
        $price = (float) ($product->selling_price ?? $product->price);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $this->truncate(strip_tags($product->short_description ?? $product->description ?? ''), 500),
            'sku' => $product->sku,
            'image' => $image,
            'url' => route('products.show', $product),
            'brand' => [
                '@type' => 'Brand',
                'name' => $product->brand?->name ?? config('seo.site_name'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('products.show', $product),
                'priceCurrency' => 'INR',
                'price' => number_format($price, 2, '.', ''),
                'availability' => $product->isInStock()
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ],
        ];
    }
}
