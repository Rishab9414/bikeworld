<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReferral extends Model
{
    protected $fillable = ['referrer_id', 'referred_id', 'status', 'reward_amount', 'reward_points'];

    protected function casts(): array
    {
        return ['reward_amount' => 'decimal:2'];
    }

    public function referrer(): BelongsTo { return $this->belongsTo(Customer::class, 'referrer_id'); }
    public function referred(): BelongsTo { return $this->belongsTo(Customer::class, 'referred_id'); }
}
