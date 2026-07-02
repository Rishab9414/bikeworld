<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id', 'address_type', 'full_name', 'mobile', 'alternate_mobile',
        'address_line_1', 'address_line_2', 'landmark', 'city', 'district',
        'state', 'country', 'pincode', 'latitude', 'longitude',
        'is_default_shipping', 'is_default_billing', 'status',
    ];

    protected function casts(): array
    {
        return [
            'is_default_shipping' => 'boolean',
            'is_default_billing' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }

    public function fullAddress(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            $this->landmark,
            $this->city,
            $this->district,
            $this->state,
            $this->pincode,
            $this->country,
        ])->filter()->implode(', ');
    }
}
