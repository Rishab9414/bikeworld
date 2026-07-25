<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role_id',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function loginHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function cartItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isSuperAdmin(): bool
    {
        return optional($this->role)->slug === 'super-admin';
    }

    protected function activeRolePermissions(): \Illuminate\Support\Collection
    {
        if (! $this->role_id) {
            return collect();
        }

        $this->loadMissing('role.permissions');

        if (! $this->role || ! $this->role->status) {
            return collect();
        }

        return $this->role->permissions;
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->activeRolePermissions()->contains('slug', $slug);
    }

    public function hasAnyPermission(array $slugs): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->activeRolePermissions()
            ->pluck('slug')
            ->intersect($slugs)
            ->isNotEmpty();
    }

    public function hasPermissionGroup(string $group): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->activeRolePermissions()->contains('group', $group);
    }

    /**
     * Send the password reset notification with a secure hashed token link.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
