<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_reels', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('thumbnail')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('home_reels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
