<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class HomeTheme extends Model
{
    public const CACHE_KEY = 'home_theme.current';

    protected $fillable = [
        'name', 'slug', 'preset',
        'primary_color', 'secondary_color', 'accent_color', 'ticker_bg_color',
        'decoration', 'hero_overlay', 'hero_badge_text',
        'priority', 'starts_at', 'ends_at', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'priority' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function presetLabel(): string
    {
        return config("home-themes.presets.{$this->preset}.label", ucfirst(str_replace('_', ' ', $this->preset)));
    }

    public function decorationLabel(): string
    {
        return config("home-themes.decorations.{$this->decoration}", ucfirst($this->decoration));
    }

    public function isLive(): bool
    {
        $now = now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }

        return true;
    }

    public function scheduleLabel(): string
    {
        $from = $this->starts_at?->format('d M Y, H:i') ?? 'Anytime';
        $until = $this->ends_at?->format('d M Y, H:i') ?? 'No end';

        return "{$from} → {$until}";
    }

    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    public static function current(): ?self
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function () {
            return static::query()
                ->active()
                ->orderByDesc('priority')
                ->latest('updated_at')
                ->first();
        });
    }

    public static function applyPreset(string $preset): array
    {
        $defaults = config('home-themes.presets.default', []);
        $presetData = config("home-themes.presets.{$preset}", []);

        return array_merge($defaults, $presetData);
    }

    public function cssVariables(): array
    {
        return [
            '--brand-red' => $this->primary_color,
            '--brand-black' => $this->secondary_color,
            '--brand-dark' => $this->accent_color,
            '--theme-ticker-bg' => $this->ticker_bg_color,
        ];
    }

    public function cssVariablesString(): string
    {
        return collect($this->cssVariables())
            ->map(fn ($value, $key) => "{$key}: {$value}")
            ->implode('; ');
    }

    public function heroOverlayClasses(): string
    {
        return match ($this->hero_overlay) {
            'warm' => 'from-amber-900/50 via-brand-black/20 to-transparent',
            'colorful' => 'from-fuchsia-600/40 via-brand-black/15 to-cyan-500/30',
            'festive' => 'from-emerald-900/45 via-brand-black/15 to-red-900/40',
            'patriotic' => 'from-orange-600/35 via-brand-black/15 to-green-700/35',
            'night' => 'from-indigo-950/55 via-brand-black/20 to-transparent',
            default => 'from-brand-black/45 via-brand-black/15 to-transparent',
        };
    }

    public static function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'theme';
        $slug = $base;
        $counter = 1;

        while (
            static::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
