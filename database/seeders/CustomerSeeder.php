<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use App\Services\LoyaltyService;
use App\Services\WalletService;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $walletService = app(WalletService::class);
        $loyaltyService = app(LoyaltyService::class);

        $users = User::where('is_admin', false)->get();

        foreach ($users as $user) {
            if (Customer::where('user_id', $user->id)->exists()) {
                continue;
            }

            $customer = Customer::fromUser($user, [
                'mobile' => $user->phone ?? fake()->numerify('98########'),
                'registration_source' => 'website',
            ]);

            $walletService->credit($customer->wallet, 500, 'Welcome wallet credit', 'promotion');
            $loyaltyService->earn($customer->loyaltyPoint, config('loyalty.earning.registration', 100), 'Registration bonus', 'registration');
        }

        if (Customer::count() === 0) {
            Customer::create([
                'first_name' => 'Rahul',
                'last_name' => 'Sharma',
                'email' => 'rahul@example.com',
                'mobile' => '9876543210',
                'password' => 'password',
                'registration_source' => 'admin',
                'email_verified' => true,
                'mobile_verified' => true,
                'customer_type' => 'vip',
                'loyalty_tier' => 'silver',
            ]);
        }
    }
}
