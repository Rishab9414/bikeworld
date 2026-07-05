<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandImageGenerator
{
    private const PALETTES = [
        ['#1e1b4b', '#6366f1'],
        ['#0f172a', '#ef4444'],
        ['#14532d', '#22c55e'],
        ['#431407', '#f97316'],
        ['#172554', '#3b82f6'],
        ['#4c0519', '#ec4899'],
        ['#134e4a', '#14b8a6'],
        ['#312e81', '#a855f7'],
        ['#713f12', '#eab308'],
        ['#1e293b', '#64748b'],
    ];

    public function generate(string $brandName, ?string $slug = null): string
    {
        $slug = $slug ?: Str::slug($brandName);
        $palette = self::PALETTES[crc32($slug) % count(self::PALETTES)];
        [$bg, $accent] = $palette;
        $initials = $this->initials($brandName);
        $safeName = htmlspecialchars($brandName, ENT_XML1);
        $safeInitials = htmlspecialchars($initials, ENT_XML1);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{$bg}"/>
      <stop offset="100%" style="stop-color:{$accent}"/>
    </linearGradient>
  </defs>
  <rect width="400" height="400" fill="url(#bg)" rx="24"/>
  <circle cx="200" cy="155" r="70" fill="rgba(255,255,255,0.12)"/>
  <text x="200" y="175" text-anchor="middle" fill="#ffffff" font-family="Arial,Helvetica,sans-serif" font-size="52" font-weight="700">{$safeInitials}</text>
  <text x="200" y="280" text-anchor="middle" fill="rgba(255,255,255,0.95)" font-family="Arial,Helvetica,sans-serif" font-size="22" font-weight="600">{$safeName}</text>
  <path d="M95 330 Q200 300 305 330" stroke="rgba(255,255,255,0.35)" stroke-width="3" fill="none"/>
</svg>
SVG;

        $path = "vehicle-brands/{$slug}.svg";
        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1).substr($parts[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }
}
