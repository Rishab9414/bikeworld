<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            $table->string('banner_image')->nullable()->after('image');
            $table->string('seo_title')->nullable()->after('description');
            $table->text('seo_keywords')->nullable()->after('seo_title');
            $table->text('meta_description')->nullable()->after('seo_keywords');
            $table->unsignedInteger('display_order')->default(0)->after('meta_description');
            $table->boolean('featured')->default(false)->after('display_order');
            $table->boolean('show_in_menu')->default(true)->after('featured');
            $table->string('status')->default('active')->after('show_in_menu');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn([
                'banner_image', 'seo_title', 'seo_keywords', 'meta_description',
                'display_order', 'featured', 'show_in_menu', 'status',
            ]);
        });
    }
};
