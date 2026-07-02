<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->insert([
            'key' => 'default_tax_included',
            'value' => '0',
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'default_tax_included')->delete();
    }
};
