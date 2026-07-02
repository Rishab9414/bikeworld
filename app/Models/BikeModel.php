<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BikeModel extends Model
{
    protected $fillable = [
        'vehicle_brand_id', 'name', 'slug', 'year', 'engine_cc', 'status',
    ];

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
}
