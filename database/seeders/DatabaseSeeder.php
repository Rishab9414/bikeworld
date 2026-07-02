<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            MasterDataSeeder::class,
            ProductSeeder::class,
            BannerSeeder::class,
            AnnouncementSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@bikeworld.com',
            'password' => bcrypt('password'),
        ]);

        $this->call(CustomerSeeder::class);
        $this->call(OrderSeeder::class);
    }
}
