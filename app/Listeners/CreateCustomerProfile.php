<?php

namespace App\Listeners;

use App\Models\Customer;
use App\Models\CustomerLoginLog;
use App\Services\LoyaltyService;
use Illuminate\Auth\Events\Registered;

class CreateCustomerProfile
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        if ($user->is_admin || Customer::where('user_id', $user->id)->exists()) {
            return;
        }

        $customer = Customer::fromUser($user);

        try {
            app(LoyaltyService::class)->earn(
                $customer->loyaltyPoint,
                config('loyalty.earning.registration', 100),
                'Registration bonus',
                'registration'
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
