<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $fillable = [
        'name', 'slug', 'logo', 'banner', 'description', 'website', 'country',
        'seo_title', 'seo_keywords', 'meta_description', 'status',
    ];

    protected static function booted(): void
    {
        static::creating(fn (Brand $b) => $b->slug ??= Str::slug($b->name));
        static::updating(function (Brand $b) {
            if ($b->isDirty('name') && ! $b->isDirty('slug')) {
                $b->slug = Str::slug($b->name);
            }
        });
    }
}
