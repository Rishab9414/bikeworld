<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeTheme;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeThemeController extends Controller
{
    public function index(): View
    {
        $themes = HomeTheme::query()->latest()->get();
        $liveTheme = HomeTheme::current();

        return view('admin.home-themes.index', compact('themes', 'liveTheme'));
    }

    public function create(): View
    {
        return view('admin.home-themes.form', [
            'theme' => new HomeTheme([
                'is_active' => true,
                'preset' => 'default',
                'priority' => 0,
                ...HomeTheme::applyPreset('default'),
            ]),
            'presets' => config('home-themes.presets', []),
            'decorations' => config('home-themes.decorations', []),
            'heroOverlays' => config('home-themes.hero_overlays', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTheme($request);
        $data['slug'] = HomeTheme::generateSlug($data['name']);

        $theme = HomeTheme::create($data);
        ActivityLogger::log('created', 'home-themes', $theme, "Home theme {$theme->name} created");

        return redirect()->route('admin.home-themes.index')->with('success', 'Home theme created.');
    }

    public function edit(HomeTheme $homeTheme): View
    {
        return view('admin.home-themes.form', [
            'theme' => $homeTheme,
            'presets' => config('home-themes.presets', []),
            'decorations' => config('home-themes.decorations', []),
            'heroOverlays' => config('home-themes.hero_overlays', []),
        ]);
    }

    public function update(Request $request, HomeTheme $homeTheme): RedirectResponse
    {
        $data = $this->validateTheme($request);

        if ($homeTheme->name !== $data['name']) {
            $data['slug'] = HomeTheme::generateSlug($data['name'], $homeTheme->id);
        }

        $homeTheme->update($data);
        ActivityLogger::log('updated', 'home-themes', $homeTheme, "Home theme {$homeTheme->name} updated");

        return redirect()->route('admin.home-themes.index')->with('success', 'Home theme updated.');
    }

    public function destroy(HomeTheme $homeTheme): RedirectResponse
    {
        $name = $homeTheme->name;
        $homeTheme->delete();
        ActivityLogger::log('deleted', 'home-themes', null, "Home theme {$name} deleted");

        return redirect()->route('admin.home-themes.index')->with('success', 'Home theme deleted.');
    }

    private function validateTheme(Request $request): array
    {
        $presets = array_keys(config('home-themes.presets', []));
        $decorations = array_keys(config('home-themes.decorations', []));
        $overlays = array_keys(config('home-themes.hero_overlays', []));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'preset' => ['required', 'string', 'in:'.implode(',', $presets)],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'ticker_bg_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'decoration' => ['required', 'string', 'in:'.implode(',', $decorations)],
            'hero_overlay' => ['required', 'string', 'in:'.implode(',', $overlays)],
            'hero_badge_text' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:999'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['priority'] = (int) ($validated['priority'] ?? 0);

        return $validated;
    }
}
