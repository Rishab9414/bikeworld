<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('preset')->default('default');
            $table->string('primary_color', 7)->default('#E31E24');
            $table->string('secondary_color', 7)->default('#0A0A0A');
            $table->string('accent_color', 7)->default('#141414');
            $table->string('ticker_bg_color', 7)->default('#E31E24');
            $table->string('decoration')->default('none');
            $table->string('hero_overlay')->default('default');
            $table->string('hero_badge_text')->nullable();
            $table->unsignedSmallInteger('priority')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_themes');
    }
};
