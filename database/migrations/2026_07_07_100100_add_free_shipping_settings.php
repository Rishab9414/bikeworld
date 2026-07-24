<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')->insertOrIgnore([
            ['key' => 'free_shipping_enabled', 'value' => '0'],
            ['key' => 'free_shipping_min_amount', 'value' => '5000'],
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')->whereIn('key', [
            'free_shipping_enabled',
            'free_shipping_min_amount',
        ])->delete();
    }
};
