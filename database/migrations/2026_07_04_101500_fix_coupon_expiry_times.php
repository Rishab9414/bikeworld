<?php

use App\Models\Coupon;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Coupon::whereNotNull('expires_at')->each(function (Coupon $coupon) {
            $coupon->update(['expires_at' => $coupon->expires_at->copy()->endOfDay()]);
        });

        Coupon::whereNotNull('starts_at')->each(function (Coupon $coupon) {
            $coupon->update(['starts_at' => $coupon->starts_at->copy()->startOfDay()]);
        });
    }

    public function down(): void
    {
        // no rollback needed
    }
};
