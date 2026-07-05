<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class BikeModel extends Model
{
    protected $fillable = [
        'vehicle_brand_id', 'name', 'slug', 'image', 'year', 'engine_cc', 'status', 'show_in_shop', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'show_in_shop' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BikeModel $m) {
            $m->slug ??= Str::slug($m->name);
        });
    }

    public function vehicleBrand(): BelongsTo
    {
        return $this->belongsTo(VehicleBrand::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_bike_model');
    }

    public function imageUrl(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        return str_starts_with($this->image, 'http') ? $this->image : asset('storage/'.$this->image);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
