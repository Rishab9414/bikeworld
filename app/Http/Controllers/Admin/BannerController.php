<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = Banner::with('category')->orderBy('sort_order')->get();

        return view('admin.banners.index', compact('banners'));
    }

    public function create(): View
    {
        return view('admin.banners.form', [
            'banner' => new Banner(['is_active' => true, 'button_text' => 'Shop Now']),
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateBanner($request);
        $data['image'] = $this->resolveBannerImage($request, $data['image'] ?? null);

        if ($data['image'] === null || $data['image'] === '') {
            return back()->withErrors(['image' => 'Provide an image URL or upload a file.'])->withInput();
        }

        $banner = Banner::create($data);
        ActivityLogger::log('created', 'banners', $banner, "Banner {$banner->title} created");

        return redirect()->route('admin.banners.index')->with('success', 'Banner created.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.form', [
            'banner' => $banner,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $this->validateBanner($request);

        $resolvedImage = $this->resolveBannerImage($request, $data['image'] ?? $banner->image);
        if ($resolvedImage !== null && $resolvedImage !== '') {
            if ($request->hasFile('image_file') && $banner->image && ! str_starts_with($banner->image, 'http')) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $resolvedImage;
        } else {
            unset($data['image']);
        }

        $banner->update($data);
        ActivityLogger::log('updated', 'banners', $banner, "Banner {$banner->title} updated");

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image && ! str_starts_with($banner->image, 'http')) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted.');
    }

    private function validateBanner(Request $request): array
    {
        $id = $request->route('banner')?->id;

        $request->merge([
            'category_id' => $request->input('category_id') ?: null,
            'link_url' => $request->input('link_url') ?: null,
            'subtitle' => $request->input('subtitle') ?: null,
            'image' => $request->input('image') ?: null,
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'image' => [$id ? 'nullable' : 'required_without:image_file', 'nullable', 'string', 'max:1000'],
            'image_file' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'button_text' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'image.required_without' => 'Upload a banner image or paste an image URL.',
            'image_file.image' => 'The uploaded file must be an image (JPG, PNG, or WebP).',
            'link_url.url' => 'Custom link must be a valid URL starting with http:// or https://',
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }

    private function resolveBannerImage(Request $request, ?string $imageUrl): ?string
    {
        $file = $request->file('image_file');

        if ($file instanceof UploadedFile && $file->isValid()) {
            return $file->store('banners', 'public');
        }

        $imageUrl = trim((string) $imageUrl);

        return $imageUrl !== '' ? $imageUrl : null;
    }

    private function categoryOptions()
    {
        $root = Category::where('slug', 'bike-accessories')->first();

        return Category::query()
            ->when($root, fn ($query) => $query->where('parent_id', $root->id)->orWhere('id', $root->id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
