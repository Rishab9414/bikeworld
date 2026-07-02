<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        if (Announcement::count() > 0) {
            return;
        }

        $items = [
            // Top black bar
            ['text' => 'Free shipping on orders above ₹2,000', 'icon' => '🚚', 'position' => 'top_bar', 'type' => 'info', 'sort_order' => 1],
            ['text' => 'Support: +91 98765 43210 · COD Available', 'icon' => '📞', 'position' => 'top_bar', 'type' => 'info', 'sort_order' => 2],

            // Red scrolling ticker
            ['text' => 'Helmet Mega Sale — Up to 40% Off | Use code HELMET40', 'icon' => '🔥', 'position' => 'ticker', 'type' => 'promo', 'sort_order' => 1, 'link_url' => '/products?category=helmet'],
            ['text' => '100% Genuine Products', 'icon' => '✓', 'position' => 'ticker', 'type' => 'trust', 'sort_order' => 2],
            ['text' => 'Pan-India Delivery', 'icon' => '🚚', 'position' => 'ticker', 'type' => 'trust', 'sort_order' => 3],
            ['text' => 'COD & Online Payment', 'icon' => '💳', 'position' => 'ticker', 'type' => 'trust', 'sort_order' => 4],
            ['text' => '7-Day Easy Returns', 'icon' => '↩', 'position' => 'ticker', 'type' => 'trust', 'sort_order' => 5],
            ['text' => 'ISI Certified Helmets', 'icon' => '🛡️', 'position' => 'ticker', 'type' => 'trust', 'sort_order' => 6],
            ['text' => 'Same-Day Dispatch', 'icon' => '⚡', 'position' => 'ticker', 'type' => 'trust', 'sort_order' => 7],
        ];

        foreach ($items as $item) {
            Announcement::create($item + ['is_active' => true]);
        }

        $this->command->info('Created '.count($items).' store announcements.');
    }
}
