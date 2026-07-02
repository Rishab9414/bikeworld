<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'user_id', 'customer_id', 'session_id', 'product_id', 'variant_id',
        'quantity', 'unit_price', 'discount', 'total', 'saved_for_later',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'saved_for_later' => 'boolean',
        ];
    }

    public function cart(): BelongsTo { return $this->belongsTo(Cart::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'variant_id'); }

    public function lineTotal(): float
    {
        if ($this->total !== null) {
            return (float) $this->total;
        }

        $price = $this->unit_price ?? $this->product?->price ?? 0;

        return (float) $price * $this->quantity - (float) ($this->discount ?? 0);
    }

    public function subtotal(): float
    {
        return $this->lineTotal();
    }
}
