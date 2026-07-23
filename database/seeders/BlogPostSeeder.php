<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title' => 'How to Choose the Right Helmet Size for Indian Riders',
                'slug' => 'how-to-choose-helmet-size-india',
                'excerpt' => 'A complete guide to measuring your head and picking the perfect full-face or half-face helmet for safe riding.',
                'content' => "Choosing the correct helmet size is critical for safety and comfort on Indian roads.\n\n## Measure your head\nWrap a soft tape measure around your head, about 1 inch above your eyebrows. Note the measurement in centimetres.\n\n## Check the size chart\nEvery helmet brand has a slightly different fit. Always compare your measurement with the manufacturer's size chart before ordering.\n\n## Try before you ride\nThe helmet should fit snugly without pressure points. It should not move when you shake your head.\n\n## ISI & safety standards\nLook for ISI-certified helmets for legal riding in India. Full-face helmets offer the best protection for highway speeds.",
                'meta_title' => 'Helmet Size Guide India — How to Choose the Right Fit | BikeWorld',
                'meta_description' => 'Learn how to measure your head and choose the perfect helmet size. ISI-certified helmet buying guide for Indian riders.',
                'meta_keywords' => 'helmet size guide india, how to choose helmet, ISI helmet, full face helmet size',
                'status' => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Top 5 Must-Have Bike Accessories for Daily Commuters',
                'slug' => 'must-have-bike-accessories-daily-commute',
                'excerpt' => 'From riding gloves to chain lube — essential gear every daily rider in India should own.',
                'content' => "Daily commuting on a motorcycle in India means heat, dust, traffic, and unpredictable weather. Here are five accessories worth investing in.\n\n## 1. Quality riding gloves\nProtect your hands and improve grip in monsoon conditions.\n\n## 2. Helmet with good ventilation\nIndian summers demand airflow. Look for vents and removable liners.\n\n## 3. Chain lube & cleaner\nRegular chain care extends life and improves mileage.\n\n## 4. Phone mount or tank bag\nNavigate safely without holding your phone.\n\n## 5. Reflective gear\nVisibility saves lives during early morning and night rides.",
                'meta_title' => '5 Must-Have Bike Accessories for Daily Commuters | BikeWorld Blog',
                'meta_description' => 'Essential motorcycle accessories for Indian daily commuters — gloves, helmets, chain care & more.',
                'meta_keywords' => 'bike accessories commute india, daily rider gear, motorcycle essentials',
                'status' => 'published',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Monsoon Riding Tips: Keep Your Bike & Gear in Top Shape',
                'slug' => 'monsoon-riding-tips-bike-care',
                'excerpt' => 'Prepare your motorcycle and riding gear for the rainy season with these practical maintenance tips.',
                'content' => "Monsoon riding in India requires extra care for both rider and machine.\n\n## Before the season\n- Apply anti-rust spray on exposed metal\n- Check brake pads and tyre tread\n- Waterproof your riding jacket and boots\n\n## During rides\n- Reduce speed on wet roads\n- Avoid painted road markings and manhole covers\n- Keep visor anti-fog spray handy\n\n## After every wet ride\n- Wipe down the chain and apply fresh lube\n- Dry your gloves and helmet padding\n- Check electrical connections for moisture",
                'meta_title' => 'Monsoon Riding Tips & Bike Care Guide | BikeWorld',
                'meta_description' => 'Monsoon motorcycle care tips for Indian riders. Keep your bike and riding gear safe in the rainy season.',
                'meta_keywords' => 'monsoon riding tips, bike care monsoon india, motorcycle rain riding',
                'status' => 'published',
                'published_at' => now()->subDay(),
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::updateOrCreate(['slug' => $post['slug']], $post);
        }
    }
}
