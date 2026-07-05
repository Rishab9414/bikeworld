<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\SeoService;
use Illuminate\View\View;

class PageController extends Controller
{
    private const PAGES = [
        'privacy-policy' => 'Privacy Policy',
        'terms-and-conditions' => 'Terms & Conditions',
        'shipping-policy' => 'Shipping Policy',
        'return-refund-policy' => 'Return & Refund Policy',
        'cancellation-policy' => 'Cancellation Policy',
    ];

    public function show(string $slug): View
    {
        abort_unless(isset(self::PAGES[$slug]), 404);

        return view('shop.pages.'.$slug, [
            'pageTitle' => self::PAGES[$slug],
            'seo' => app(SeoService::class)->forPage($slug),
        ]);
    }

    public static function links(): array
    {
        return collect(self::PAGES)->map(fn ($title, $slug) => [
            'slug' => $slug,
            'title' => $title,
            'url' => route('pages.show', $slug),
        ])->values()->all();
    }
}
