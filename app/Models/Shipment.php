<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    protected $fillable = [
        'order_id', 'courier_name', 'shipment_id', 'waybill', 'tracking_number',
        'tracking_url', 'pickup_request_id', 'shipping_label', 'manifest',
        'shipping_cost', 'estimated_delivery', 'pickup_date', 'shipment_status',
    ];

    protected function casts(): array
    {
        return [
            'shipping_cost' => 'decimal:2',
            'estimated_delivery' => 'date',
            'pickup_date' => 'date',
        ];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function tracking(): HasMany { return $this->hasMany(ShipmentTracking::class); }

    public function statusLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->shipment_status));
    }
}
