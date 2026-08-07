<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'image', 'category_id', 'link_url',
        'button_text', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function imageUrl(): string
    {
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'images/')) {
            return asset($this->image);
        }

        return asset('storage/'.$this->image);
    }

    public function targetUrl(): string
    {
        if ($this->category_id && $this->category) {
            return route('products.index', ['category' => $this->category->slug]);
        }

        return $this->link_url ?? route('products.index');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public static function recommendedSizeLabel(): string
    {
        return config('banners.recommended_width').' × '.config('banners.recommended_height').' px';
    }

    public function imageDimensionsLabel(): string
    {
        $path = $this->resolveLocalImagePath();

        if (! $path || ! is_file($path)) {
            return '—';
        }

        $size = @getimagesize($path);

        if (! $size) {
            return '—';
        }

        return $size[0].' × '.$size[1];
    }

    public function matchesRecommendedSize(): bool
    {
        $path = $this->resolveLocalImagePath();

        if (! $path || ! is_file($path)) {
            return false;
        }

        $size = @getimagesize($path);

        if (! $size) {
            return false;
        }

        [$width, $height] = $size;

        return $width >= config('banners.min_width', 1600)
            && $height >= config('banners.min_height', 520);
    }

    private function resolveLocalImagePath(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return null;
        }

        if (str_starts_with($this->image, 'images/')) {
            $path = public_path($this->image);

            return is_file($path) ? $path : null;
        }

        if (Storage::disk('public')->exists($this->image)) {
            return Storage::disk('public')->path($this->image);
        }

        return null;
    }
}
