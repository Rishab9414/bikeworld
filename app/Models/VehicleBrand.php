<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VehicleBrand extends Model
{
    protected $fillable = ['name', 'slug', 'logo', 'status'];

    protected static function booted(): void
    {
        static::creating(fn (VehicleBrand $b) => $b->slug ??= Str::slug($b->name));
    }

    public function bikeModels(): HasMany
    {
        return $this->hasMany(BikeModel::class);
    }
}
