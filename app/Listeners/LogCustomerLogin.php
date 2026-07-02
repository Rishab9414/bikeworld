<?php

namespace App\Listeners;

use App\Models\Customer;
use App\Models\CustomerLoginLog;
use Illuminate\Auth\Events\Login;

class LogCustomerLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if ($user->is_admin) {
            return;
        }

        $customer = Customer::where('user_id', $user->id)->first();

        if (! $customer) {
            return;
        }

        $customer->update([
            'last_login' => now(),
            'last_login_ip' => request()->ip(),
            'device_type' => $this->deviceType(),
        ]);

        CustomerLoginLog::create([
            'customer_id' => $customer->id,
            'ip_address' => request()->ip(),
            'device_type' => $this->deviceType(),
            'browser' => request()->userAgent(),
            'logged_in_at' => now(),
        ]);
    }

    private function deviceType(): string
    {
        $ua = request()->userAgent() ?? '';

        return preg_match('/mobile|android|iphone/i', $ua) ? 'mobile' : 'desktop';
    }
}
