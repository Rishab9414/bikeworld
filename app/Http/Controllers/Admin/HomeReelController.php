<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeReel;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomeReelController extends Controller
{
    public function index(): View
    {
        $reels = HomeReel::with('category')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.home-reels.index', compact('reels'));
    }

    public function create(): View
    {
        return view('admin.home-reels.form', [
            'reel' => new HomeReel(['is_active' => true, 'sort_order' => 0]),
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateReel($request, isUpdate: false);

        if ($request->hasFile('video_file')) {
            $data['video'] = $request->file('video_file')->store('home-reels', 'public');
        }

        if ($request->hasFile('thumbnail_file')) {
            $data['thumbnail'] = $request->file('thumbnail_file')->store('home-reels/thumbnails', 'public');
        }

        unset($data['video_file'], $data['thumbnail_file']);
        $data['category_id'] = $request->input('category_id') ?: null;

        if (empty($data['video'])) {
            return back()->withErrors(['video_file' => 'Please upload a video file.'])->withInput();
        }

        $reel = HomeReel::create($data);
        ActivityLogger::log('created', 'home_reels', $reel, "Home reel {$reel->title} created");

        return redirect()->route('admin.home-reels.index')->with('success', 'Reel video created.');
    }

    public function edit(HomeReel $homeReel): View
    {
        return view('admin.home-reels.form', [
            'reel' => $homeReel,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, HomeReel $homeReel): RedirectResponse
    {
        $data = $this->validateReel($request, isUpdate: true);

        if ($request->hasFile('video_file')) {
            $this->deleteStoredFile($homeReel->video);
            $data['video'] = $request->file('video_file')->store('home-reels', 'public');
        }

        if ($request->hasFile('thumbnail_file')) {
            $this->deleteStoredFile($homeReel->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail_file')->store('home-reels/thumbnails', 'public');
        }

        unset($data['video_file'], $data['thumbnail_file']);
        $data['category_id'] = $request->input('category_id') ?: null;

        if (empty($data['video'])) {
            unset($data['video']);
        }

        $homeReel->update($data);
        ActivityLogger::log('updated', 'home_reels', $homeReel, "Home reel {$homeReel->title} updated");

        return redirect()->route('admin.home-reels.index')->with('success', 'Reel video updated.');
    }

    public function destroy(HomeReel $homeReel): RedirectResponse
    {
        $this->deleteStoredFile($homeReel->video);
        $this->deleteStoredFile($homeReel->thumbnail);
        $homeReel->delete();

        return redirect()->route('admin.home-reels.index')->with('success', 'Reel video deleted.');
    }

    private function validateReel(Request $request, bool $isUpdate): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:50'],
            'video_file' => [$isUpdate ? 'nullable' : 'required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:51200'],
            'thumbnail_file' => ['nullable', 'image', 'max:5120'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function categoryOptions()
    {
        $root = Category::where('slug', 'bike-accessories')->first();

        return Category::where('parent_id', $root?->id)
            ->orWhere('id', $root?->id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
