<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value', 'min_order_amount', 'max_discount',
        'usage_limit', 'usage_per_customer', 'category_id', 'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    public function isCurrentlyActive(): bool
    {
        return $this->adminStatus()['key'] === 'active';
    }

    /** @return array{key: string, label: string, class: string} */
    public function adminStatus(): array
    {
        if (! $this->is_active) {
            return ['key' => 'disabled', 'label' => 'Disabled', 'class' => 'bg-slate-100 text-slate-600'];
        }

        if ($this->starts_at && now()->lt($this->starts_at->copy()->startOfDay())) {
            return ['key' => 'scheduled', 'label' => 'Scheduled', 'class' => 'bg-amber-100 text-amber-700'];
        }

        if ($this->expires_at && now()->gt($this->expires_at->copy()->endOfDay())) {
            return ['key' => 'expired', 'label' => 'Expired', 'class' => 'bg-red-100 text-red-700'];
        }

        return ['key' => 'active', 'label' => 'Active', 'class' => 'bg-emerald-100 text-emerald-700'];
    }

    public function totalUsageCount(): int
    {
        return $this->usages()->count();
    }

    public function customerUsageCount(?int $customerId, ?int $userId = null): int
    {
        return $this->usages()
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when(! $customerId && $userId, fn ($q) => $q->where('user_id', $userId))
            ->count();
    }

    public function typeLabel(): string
    {
        return $this->type === 'percent' ? 'Percentage' : 'Fixed amount';
    }

    public function valueLabel(): string
    {
        return $this->type === 'percent'
            ? rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.').'%'
            : '₹'.number_format((float) $this->value, 2);
    }
}
