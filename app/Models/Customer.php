<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Customer extends Model
{
    protected $fillable = [
        'user_id', 'customer_code', 'first_name', 'last_name', 'full_name',
        'email', 'mobile', 'country_code', 'password', 'profile_image',
        'gender', 'date_of_birth', 'anniversary_date', 'referral_code', 'referred_by',
        'registration_source', 'login_type', 'email_verified', 'mobile_verified',
        'account_status', 'newsletter_subscription', 'customer_type', 'loyalty_tier',
        'last_login', 'last_login_ip', 'device_type',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified' => 'boolean',
            'mobile_verified' => 'boolean',
            'newsletter_subscription' => 'boolean',
            'date_of_birth' => 'date',
            'anniversary_date' => 'date',
            'last_login' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (empty($customer->customer_code)) {
                $next = (static::max('id') ?? 0) + 1;
                $customer->customer_code = 'CUS'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            }
            if (empty($customer->referral_code)) {
                $customer->referral_code = strtoupper(Str::random(8));
            }
            $customer->full_name = trim($customer->first_name.' '.($customer->last_name ?? ''));
        });

        static::updating(function (Customer $customer) {
            if ($customer->isDirty(['first_name', 'last_name'])) {
                $customer->full_name = trim($customer->first_name.' '.($customer->last_name ?? ''));
            }
        });

        static::created(function (Customer $customer) {
            Wallet::create(['customer_id' => $customer->id]);
            LoyaltyPoint::create(['customer_id' => $customer->id]);
            CustomerPreference::create(['customer_id' => $customer->id]);
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function referrer(): BelongsTo { return $this->belongsTo(Customer::class, 'referred_by'); }
    public function referrals(): HasMany { return $this->hasMany(Customer::class, 'referred_by'); }
    public function addresses(): HasMany { return $this->hasMany(CustomerAddress::class); }
    public function wishlists(): HasMany { return $this->hasMany(Wishlist::class); }
    public function carts(): HasMany { return $this->hasMany(Cart::class); }
    public function cartItems(): HasMany { return $this->hasMany(CartItem::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function reviews(): HasMany { return $this->hasMany(ProductReview::class); }
    public function wallet(): HasOne { return $this->hasOne(Wallet::class); }
    public function loyaltyPoint(): HasOne { return $this->hasOne(LoyaltyPoint::class); }
    public function loginLogs(): HasMany { return $this->hasMany(CustomerLoginLog::class); }
    public function notifications(): HasMany { return $this->hasMany(CustomerNotification::class); }
    public function supportTickets(): HasMany { return $this->hasMany(CustomerSupportTicket::class); }
    public function preferences(): HasOne { return $this->hasOne(CustomerPreference::class); }
    public function devices(): HasMany { return $this->hasMany(CustomerDevice::class); }

    public function isActive(): bool { return $this->account_status === 'active'; }
    public function isBlocked(): bool { return $this->account_status === 'blocked'; }

    public function profileImageUrl(): ?string
    {
        return $this->profile_image ? asset('storage/'.$this->profile_image) : null;
    }

    public static function fromUser(User $user, array $extra = []): self
    {
        $nameParts = explode(' ', $user->name, 2);

        return static::create(array_merge([
            'user_id' => $user->id,
            'first_name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? null,
            'email' => $user->email,
            'mobile' => $user->phone ?? '9999999999',
            'password' => $user->password,
            'registration_source' => 'website',
            'login_type' => 'email',
            'email_verified' => (bool) $user->email_verified_at,
            'account_status' => $user->status ? 'active' : 'inactive',
        ], $extra));
    }
}
