<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@bikeworld.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('Admin@12345'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
