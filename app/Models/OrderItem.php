<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'variant_id', 'product_name', 'sku',
        'price', 'discount', 'gst', 'total', 'quantity', 'subtotal', 'weight', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount' => 'decimal:2',
            'gst' => 'decimal:2',
            'total' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'weight' => 'decimal:3',
        ];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'variant_id'); }

    public function lineTotal(): float
    {
        return (float) ($this->total ?? $this->subtotal);
    }
}
