<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('event', 64);
            $table->string('recipient');
            $table->string('subject');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['order_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_email_logs');
    }
};
