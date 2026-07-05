<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            ['key' => 'free_shipping_enabled', 'value' => '0'],
            ['key' => 'free_shipping_min_amount', 'value' => '5000'],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'free_shipping_enabled',
            'free_shipping_min_amount',
        ])->delete();
    }
};
