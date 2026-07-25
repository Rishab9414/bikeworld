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

    public function maintenance(): View
    {
        return view('admin.settings.maintenance', [
            'isDown' => app()->isDownForMaintenance(),
            'message' => Setting::maintenanceMessage(),
            'eta' => Setting::maintenanceEta(),
        ]);
    }

    public function updateMaintenance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'message' => ['nullable', 'string', 'max:500'],
            'eta' => ['nullable', 'date'],
        ]);

        $enabled = $request->boolean('enabled');
        $message = trim((string) ($validated['message'] ?? ''))
            ?: 'We are upgrading BikeWorld for a smoother ride. Please check back shortly.';
        $eta = $validated['eta'] ?? null;

        Setting::set('maintenance_message', $message);
        Setting::set('maintenance_eta', $eta ? (string) $eta : '');

        if ($enabled) {
            \Illuminate\Support\Facades\Artisan::call('down', [
                '--render' => 'errors.maintenance',
                '--retry' => 60,
                '--refresh' => 30,
                '--secret' => 'bikeworld-admin-bypass',
            ]);

            ActivityLogger::log('updated', 'settings', null, 'Website maintenance mode ENABLED');

            return back()->with('success', 'Maintenance mode is ON. Customers see the maintenance page. Admin panel still works.');
        }

        \Illuminate\Support\Facades\Artisan::call('up');

        ActivityLogger::log('updated', 'settings', null, 'Website maintenance mode DISABLED');

        return back()->with('success', 'Maintenance mode is OFF. The storefront is live again.');
    }
}
