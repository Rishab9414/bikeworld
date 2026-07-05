<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('full_name');
            $table->index('mobile');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['full_name']);
            $table->dropIndex(['mobile']);
        });
    }
};
