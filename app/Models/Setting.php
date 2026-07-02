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
}
