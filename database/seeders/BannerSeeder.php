<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'GEAR UP FOR THE RIDE',
                'subtitle' => 'Premium helmets, riding gear & accessories — delivered across India',
                'image' => 'images/banners/banner-ride-hero.png',
                'category_id' => null,
                'link_url' => null,
                'button_text' => 'Shop Now',
                'sort_order' => 1,
            ],
            [
                'title' => 'RIDE WITH CONFIDENCE',
                'subtitle' => 'Genuine brands. Best prices. Everything a rider needs in one place',
                'image' => 'images/banners/banner-gear-hero.png',
                'category_id' => null,
                'link_url' => null,
                'button_text' => 'Explore Collection',
                'sort_order' => 2,
            ],
            [
                'title' => 'YOUR RIDE. YOUR GEAR.',
                'subtitle' => 'From daily commute to weekend adventures — we\'ve got you covered',
                'image' => 'images/banners/banner-adventure-hero.png',
                'category_id' => null,
                'link_url' => null,
                'button_text' => 'Start Shopping',
                'sort_order' => 3,
            ],
        ];

        Banner::query()->delete();

        foreach ($banners as $b) {
            Banner::create($b + ['is_active' => true]);
        }

        $this->command->info('Created '.count($banners).' homepage banners with local images.');
    }
}
