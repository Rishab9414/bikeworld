<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'barcode', 'color_id', 'size_id', 'material_id',
        'price', 'stock', 'weight', 'image', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'weight' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function color(): BelongsTo { return $this->belongsTo(Color::class); }
    public function size(): BelongsTo { return $this->belongsTo(Size::class); }
    public function material(): BelongsTo { return $this->belongsTo(Material::class); }

    public function imageUrl(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        return str_starts_with($this->image, 'http') ? $this->image : asset('storage/'.$this->image);
    }

    public function label(): string
    {
        $parts = array_filter([
            $this->color?->name,
            $this->size?->name,
            $this->material?->name,
        ]);

        return $parts !== [] ? implode(' / ', $parts) : ($this->sku ?: 'Variant #'.$this->id);
    }
}
