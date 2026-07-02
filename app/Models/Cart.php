<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'customer_id', 'session_id', 'coupon_id',
        'subtotal', 'discount', 'shipping', 'tax', 'grand_total',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function items(): HasMany { return $this->hasMany(CartItem::class); }

    public function recalculate(): void
    {
        $items = $this->items()->where('saved_for_later', false)->get();
        $subtotal = $items->sum(fn (CartItem $i) => $i->lineTotal());
        $this->update([
            'subtotal' => $subtotal,
            'grand_total' => max(0, $subtotal - $this->discount + $this->shipping + $this->tax),
        ]);
    }
}
