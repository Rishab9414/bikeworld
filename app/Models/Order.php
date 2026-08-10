<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const STATUSES = [
        'pending', 'confirmed', 'packing', 'packed', 'shipment_created', 'pickup_scheduled',
        'picked_up', 'in_transit', 'reached_destination_hub', 'out_for_delivery',
        'delivered', 'completed', 'cancelled', 'returned', 'refunded', 'exchanged',
    ];

    protected $fillable = [
        'user_id', 'customer_id', 'order_number', 'status', 'subtotal', 'discount',
        'coupon_id', 'coupon_code', 'coupon_discount', 'wallet_discount', 'shipping_charge', 'tax_amount',
        'grand_total', 'total', 'payment_method', 'payment_status',
        'razorpay_order_id', 'razorpay_payment_id', 'paid_at',
        'expected_delivery',
        'invoice_id', 'shipment_id', 'shipping_address', 'billing_address',
        'shipping_address_json', 'billing_address_json', 'notes', 'stock_reserved',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'coupon_discount' => 'decimal:2',
            'wallet_discount' => 'decimal:2',
            'shipping_charge' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'total' => 'decimal:2',
            'expected_delivery' => 'date',
            'shipping_address_json' => 'array',
            'billing_address_json' => 'array',
            'stock_reserved' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function coupon(): BelongsTo { return $this->belongsTo(Coupon::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function shipment(): HasOne { return $this->hasOne(Shipment::class); }
    public function shipmentRecord(): BelongsTo { return $this->belongsTo(Shipment::class, 'shipment_id'); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function invoiceRecord(): HasOne { return $this->hasOne(Invoice::class); }
    public function statusLogs(): HasMany { return $this->hasMany(OrderStatusLog::class)->orderBy('created_at'); }

    public function statusLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function paymentStatusLabel(): string
    {
        return ucfirst($this->payment_status);
    }

    public function displayTotal(): float
    {
        return (float) ($this->grand_total ?? $this->total);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeRevenueEligible($query)
    {
        return $query
            ->paid()
            ->whereNotIn('status', ['cancelled', 'refunded']);
    }

    public function scopePendingOrders($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAwaitingFulfillment($query)
    {
        return $query->whereNotIn('status', [
            'delivered', 'completed', 'cancelled', 'refunded', 'returned',
        ]);
    }
}
