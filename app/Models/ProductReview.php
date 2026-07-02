<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    protected $fillable = [
        'customer_id', 'product_id', 'order_item_id', 'rating', 'title', 'review',
        'images', 'video', 'verified_purchase', 'helpful_count', 'admin_reply', 'status',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'verified_purchase' => 'boolean',
        ];
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function orderItem(): BelongsTo { return $this->belongsTo(OrderItem::class); }
}
