<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Mountain Bikes',
                'slug' => 'mountain-bikes',
                'description' => 'Built for trails, hills, and off-road adventures.',
            ],
            [
                'name' => 'Road Bikes',
                'slug' => 'road-bikes',
                'description' => 'Lightweight bikes designed for speed on paved roads.',
            ],
            [
                'name' => 'Electric Bikes',
                'slug' => 'electric-bikes',
                'description' => 'Pedal-assist and throttle e-bikes for effortless riding.',
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Helmets, lights, locks, and everything you need on the road.',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
