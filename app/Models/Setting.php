<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $value = static::where('key', $key)->value('value');

            return $value !== null ? $value : $default;
        });
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        return filter_var(static::get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public static function set(string $key, mixed $value): void
    {
        $stored = is_bool($value) ? ($value ? '1' : '0') : (string) $value;

        static::updateOrCreate(['key' => $key], ['value' => $stored]);
        Cache::forget("setting.{$key}");
    }

    public static function codEnabled(): bool
    {
        return static::getBool('cod_enabled', true);
    }

    public static function defaultTaxIncluded(): bool
    {
        return static::getBool('default_tax_included', false);
    }

    public static function shopByBikeEnabled(): bool
    {
        return static::getBool('shop_by_bike_enabled', true);
    }

    public static function homeReelsEnabled(): bool
    {
        return static::getBool('home_reels_enabled', false);
    }

    public static function homeReelsAutoplay(): bool
    {
        return static::getBool('home_reels_autoplay', true);
    }

    public static function couponsEnabled(): bool
    {
        return static::getBool('coupons_enabled', true);
    }

    public static function freeShippingEnabled(): bool
    {
        return static::getBool('free_shipping_enabled', false);
    }

    public static function freeShippingMinAmount(): float
    {
        return max(0, (float) static::get('free_shipping_min_amount', 5000));
    }

    public static function qualifiesForFreeShipping(float $orderAmount): bool
    {
        return static::freeShippingEnabled()
            && $orderAmount >= static::freeShippingMinAmount();
    }
}
