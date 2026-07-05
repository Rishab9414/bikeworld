<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeReel extends Model
{
    protected $fillable = [
        'title', 'label', 'video', 'thumbnail', 'category_id', 'link_url', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function videoUrl(): string
    {
        if (str_starts_with($this->video, 'http://') || str_starts_with($this->video, 'https://')) {
            return $this->video;
        }

        return asset('storage/'.$this->video);
    }

    public function thumbnailUrl(): ?string
    {
        if (empty($this->thumbnail)) {
            return null;
        }

        if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
            return $this->thumbnail;
        }

        return asset('storage/'.$this->thumbnail);
    }

    public function targetUrl(): ?string
    {
        if ($this->category_id && $this->category) {
            return route('products.index', ['category' => $this->category->slug]);
        }

        return $this->link_url ?: null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
