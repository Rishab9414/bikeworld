<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_brands', function (Blueprint $table) {
            $table->string('image')->nullable()->after('logo');
            $table->boolean('show_in_shop')->default(true)->after('status');
            $table->unsignedInteger('sort_order')->default(0)->after('show_in_shop');
        });

        Schema::table('bike_models', function (Blueprint $table) {
            $table->string('image')->nullable()->after('slug');
            $table->boolean('show_in_shop')->default(true)->after('status');
            $table->unsignedInteger('sort_order')->default(0)->after('show_in_shop');
        });

        DB::table('settings')->insertOrIgnore([
            'key' => 'shop_by_bike_enabled',
            'value' => '1',
        ]);
    }

    public function down(): void
    {
        Schema::table('vehicle_brands', function (Blueprint $table) {
            $table->dropColumn(['image', 'show_in_shop', 'sort_order']);
        });

        Schema::table('bike_models', function (Blueprint $table) {
            $table->dropColumn(['image', 'show_in_shop', 'sort_order']);
        });

        DB::table('settings')->where('key', 'shop_by_bike_enabled')->delete();
    }
};
