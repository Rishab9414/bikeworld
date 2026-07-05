<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VehicleBrand extends Model
{
    protected $fillable = ['name', 'slug', 'logo', 'image', 'status', 'show_in_shop', 'sort_order'];

    protected function casts(): array
    {
        return [
            'show_in_shop' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (VehicleBrand $b) => $b->slug ??= Str::slug($b->name));
    }

    public function bikeModels(): HasMany
    {
        return $this->hasMany(BikeModel::class);
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
