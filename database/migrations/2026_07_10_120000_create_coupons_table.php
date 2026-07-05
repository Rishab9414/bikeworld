<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->enum('type', ['fixed', 'percent'])->default('fixed');
            $table->decimal('value', 12, 2);
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_per_customer')->nullable()->default(1);
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 12, 2);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->after('discount')->constrained()->nullOnDelete();
            $table->string('coupon_code')->nullable()->after('coupon_id');
        });

        DB::table('settings')->insertOrIgnore([
            ['key' => 'coupons_enabled', 'value' => '1'],
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn('coupon_code');
        });

        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');

        DB::table('settings')->where('key', 'coupons_enabled')->delete();
    }
};
