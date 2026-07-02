<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyPoint extends Model
{
    protected $table = 'loyalty_points';

    protected $fillable = ['customer_id', 'total_points', 'lifetime_points', 'redeemed_points'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function transactions(): HasMany { return $this->hasMany(LoyaltyTransaction::class, 'loyalty_id'); }
}
