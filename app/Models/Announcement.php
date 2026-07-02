<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Announcement extends Model
{
    public const POSITION_TOP_BAR = 'top_bar';

    public const POSITION_TICKER = 'ticker';

    protected $fillable = [
        'text', 'icon', 'link_url', 'type', 'position',
        'starts_at', 'ends_at', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function label(): string
    {
        return trim(($this->icon ? $this->icon.' ' : '').$this->text);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopePosition(Builder $query, string $position): Builder
    {
        return $query->where('position', $position);
    }

    public static function forPosition(string $position): Collection
    {
        return static::query()
            ->active()
            ->position($position)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
