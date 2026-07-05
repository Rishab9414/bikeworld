<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index('user_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('email');
            $table->index('account_status');
            $table->index('created_at');
            $table->index(['account_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['account_status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['account_status', 'created_at']);
        });
    }
};
