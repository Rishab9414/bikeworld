<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('name');
            $table->index('status');
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['status']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['brand_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });
    }
};
