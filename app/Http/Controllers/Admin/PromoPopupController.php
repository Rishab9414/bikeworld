<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoPopup;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PromoPopupController extends Controller
{
    public function index(): View
    {
        $popups = PromoPopup::latest()->get();

        return view('admin.promo-popups.index', compact('popups'));
    }

    public function create(): View
    {
        return view('admin.promo-popups.form', [
            'popup' => new PromoPopup(['is_active' => true, 'button_text' => 'Shop Now']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePopup($request);

        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('promo-popups', 'public');
        }

        unset($data['image_file']);
        if (empty($data['image'])) {
            return back()->withErrors(['image' => 'Provide an image URL or upload a file.'])->withInput();
        }

        $popup = PromoPopup::create($data);
        ActivityLogger::log('created', 'promo-popups', $popup, "Popup {$popup->title} created");

        return redirect()->route('admin.promo-popups.index')->with('success', 'Popup created.');
    }

    public function edit(PromoPopup $promoPopup): View
    {
        return view('admin.promo-popups.form', ['popup' => $promoPopup]);
    }

    public function update(Request $request, PromoPopup $promoPopup): RedirectResponse
    {
        $data = $this->validatePopup($request);

        if ($request->hasFile('image_file')) {
            if ($promoPopup->image && ! str_starts_with($promoPopup->image, 'http')) {
                Storage::disk('public')->delete($promoPopup->image);
            }
            $data['image'] = $request->file('image_file')->store('promo-popups', 'public');
        }

        unset($data['image_file']);
        if (empty($data['image'])) {
            unset($data['image']);
        }

        $promoPopup->update($data);
        ActivityLogger::log('updated', 'promo-popups', $promoPopup, "Popup {$promoPopup->title} updated");

        return redirect()->route('admin.promo-popups.index')->with('success', 'Popup updated.');
    }

    public function destroy(PromoPopup $promoPopup): RedirectResponse
    {
        if ($promoPopup->image && ! str_starts_with($promoPopup->image, 'http')) {
            Storage::disk('public')->delete($promoPopup->image);
        }
        $promoPopup->delete();

        return redirect()->route('admin.promo-popups.index')->with('success', 'Popup deleted.');
    }

    private function validatePopup(Request $request): array
    {
        $id = $request->route('promoPopup')?->id;

        $imageRules = ['nullable', 'string', 'max:1000'];
        if (! $id) {
            array_unshift($imageRules, 'required_without:image_file');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'image' => $imageRules,
            'image_file' => ['nullable', 'image', 'max:5120'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
