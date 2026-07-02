<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    protected $fillable = [
        'loyalty_id', 'transaction_type', 'points', 'reference_type',
        'reference_id', 'expiry_date', 'remarks',
    ];

    protected function casts(): array
    {
        return ['expiry_date' => 'date'];
    }

    public function loyaltyPoint(): BelongsTo { return $this->belongsTo(LoyaltyPoint::class, 'loyalty_id'); }
}
