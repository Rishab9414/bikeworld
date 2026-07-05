<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreSettingsController extends Controller
{
    public function payments(): View
    {
        return view('admin.settings.payments', [
            'codEnabled' => Setting::codEnabled(),
            'couponsEnabled' => Setting::couponsEnabled(),
            'freeShippingEnabled' => Setting::freeShippingEnabled(),
            'freeShippingMinAmount' => Setting::freeShippingMinAmount(),
        ]);
    }

    public function updatePayments(Request $request): RedirectResponse
    {
        $request->validate([
            'cod_enabled' => ['nullable', 'boolean'],
            'coupons_enabled' => ['nullable', 'boolean'],
            'free_shipping_enabled' => ['nullable', 'boolean'],
            'free_shipping_min_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $cod = $request->boolean('cod_enabled');
        $coupons = $request->boolean('coupons_enabled');
        $freeShipping = $request->boolean('free_shipping_enabled');
        $freeShippingMin = max(0, (float) $request->input('free_shipping_min_amount', 5000));

        Setting::set('cod_enabled', $cod);
        Setting::set('coupons_enabled', $coupons);
        Setting::set('free_shipping_enabled', $freeShipping);
        Setting::set('free_shipping_min_amount', $freeShippingMin);

        ActivityLogger::log(
            'updated',
            'settings',
            null,
            'Payment settings updated — COD '.($cod ? 'enabled' : 'disabled')
                .', Coupons '.($coupons ? 'enabled' : 'disabled')
                .', Free shipping '.($freeShipping ? 'enabled above ₹'.$freeShippingMin : 'disabled')
        );

        return back()->with('success', 'Payment settings saved successfully.');
    }

    public function tax(): View
    {
        return view('admin.settings.tax', [
            'defaultTaxIncluded' => Setting::defaultTaxIncluded(),
        ]);
    }

    public function updateTax(Request $request): RedirectResponse
    {
        $request->validate([
            'default_tax_included' => ['required', 'in:0,1'],
        ]);

        $included = $request->input('default_tax_included') === '1';
        Setting::set('default_tax_included', $included);

        Product::query()->update(['tax_included' => $included]);

        ActivityLogger::log(
            'updated',
            'settings',
            null,
            'Tax settings updated — default prices '.($included ? 'include GST' : 'exclude GST (added at checkout)')
        );

        return back()->with('success', 'Tax settings saved. All products updated to match the default GST price type.');
    }

    public function homepage(): View
    {
        return view('admin.settings.homepage', [
            'shopByBikeEnabled' => Setting::shopByBikeEnabled(),
            'homeReelsEnabled' => Setting::homeReelsEnabled(),
            'homeReelsAutoplay' => Setting::homeReelsAutoplay(),
        ]);
    }

    public function updateHomepage(Request $request): RedirectResponse
    {
        $request->validate([
            'shop_by_bike_enabled' => ['nullable', 'boolean'],
            'home_reels_enabled' => ['nullable', 'boolean'],
            'home_reels_autoplay' => ['nullable', 'boolean'],
        ]);

        $shopByBike = $request->boolean('shop_by_bike_enabled');
        $reelsEnabled = $request->boolean('home_reels_enabled');
        $reelsAutoplay = $request->boolean('home_reels_autoplay');

        Setting::set('shop_by_bike_enabled', $shopByBike);
        Setting::set('home_reels_enabled', $reelsEnabled);
        Setting::set('home_reels_autoplay', $reelsAutoplay);

        ActivityLogger::log(
            'updated',
            'settings',
            null,
            'Homepage settings updated — Shop by Bike '.($shopByBike ? 'enabled' : 'disabled')
                .', Reels '.($reelsEnabled ? 'enabled' : 'disabled')
                .', Reels autoplay '.($reelsAutoplay ? 'on' : 'off')
        );

        return back()->with('success', 'Homepage settings saved successfully.');
    }
}
