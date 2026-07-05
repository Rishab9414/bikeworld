<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_reels', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('label')->nullable();
            $table->string('video');
            $table->string('thumbnail')->nullable();
            $table->string('link_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('settings')->insertOrIgnore([
            ['key' => 'home_reels_enabled', 'value' => '0'],
            ['key' => 'home_reels_autoplay', 'value' => '1'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('home_reels');

        DB::table('settings')->whereIn('key', ['home_reels_enabled', 'home_reels_autoplay'])->delete();
    }
};
