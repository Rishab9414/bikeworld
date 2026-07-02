<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        ]);
    }

    public function updatePayments(Request $request): RedirectResponse
    {
        $request->validate([
            'cod_enabled' => ['nullable', 'boolean'],
        ]);

        $enabled = $request->boolean('cod_enabled');
        Setting::set('cod_enabled', $enabled);

        ActivityLogger::log(
            'updated',
            'settings',
            null,
            'Payment settings updated — COD '.($enabled ? 'enabled' : 'disabled')
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

        ActivityLogger::log(
            'updated',
            'settings',
            null,
            'Tax settings updated — default prices '.($included ? 'include GST' : 'exclude GST (added at checkout)')
        );

        return back()->with('success', 'Tax settings saved successfully.');
    }
}
