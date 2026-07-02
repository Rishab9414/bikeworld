<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('sub_category_id')->nullable()->after('category_id')->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->after('sub_category_id')->constrained()->nullOnDelete();
            $table->foreignId('manufacturer_id')->nullable()->after('brand_id')->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->after('manufacturer_id')->constrained()->nullOnDelete();
            $table->foreignId('tax_id')->nullable()->after('supplier_id')->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->after('tax_id')->constrained()->nullOnDelete();

            $table->string('short_name')->nullable()->after('name');
            $table->string('sku')->nullable()->unique()->after('slug');
            $table->string('barcode')->nullable()->after('sku');
            $table->string('qr_code')->nullable()->after('barcode');
            $table->string('product_type')->default('simple')->after('qr_code');
            $table->string('product_condition')->default('new')->after('product_type');
            $table->string('hsn_code')->nullable()->after('product_condition');
            $table->string('country_of_origin')->nullable()->after('hsn_code');
            $table->string('warranty')->nullable()->after('country_of_origin');
            $table->unsignedInteger('return_days')->nullable()->after('warranty');
            $table->unsignedInteger('replace_days')->nullable()->after('return_days');
            $table->unsignedInteger('min_order_qty')->default(1)->after('replace_days');
            $table->unsignedInteger('max_order_qty')->nullable()->after('min_order_qty');

            $table->text('short_description')->nullable()->after('description');
            $table->longText('long_description')->nullable()->after('short_description');
            $table->longText('specification')->nullable()->after('long_description');
            $table->text('installation_guide')->nullable()->after('specification');
            $table->text('box_contents')->nullable()->after('installation_guide');
            $table->text('care_instructions')->nullable()->after('box_contents');
            $table->text('warranty_info')->nullable()->after('care_instructions');

            $table->decimal('purchase_price', 12, 2)->nullable()->after('compare_price');
            $table->decimal('landing_cost', 12, 2)->nullable()->after('purchase_price');
            $table->decimal('selling_price', 12, 2)->nullable()->after('landing_cost');
            $table->decimal('mrp', 12, 2)->nullable()->after('selling_price');
            $table->decimal('discount_percent', 5, 2)->nullable()->after('mrp');
            $table->decimal('offer_price', 12, 2)->nullable()->after('discount_percent');
            $table->decimal('dealer_price', 12, 2)->nullable()->after('offer_price');
            $table->decimal('wholesale_price', 12, 2)->nullable()->after('dealer_price');
            $table->boolean('tax_included')->default(false)->after('wholesale_price');
            $table->decimal('commission', 8, 2)->nullable()->after('tax_included');

            $table->unsignedInteger('reserved_stock')->default(0)->after('stock');
            $table->unsignedInteger('low_stock_alert')->default(5)->after('reserved_stock');
            $table->string('warehouse')->nullable()->after('low_stock_alert');
            $table->string('rack_number')->nullable()->after('warehouse');
            $table->unsignedInteger('reorder_level')->nullable()->after('rack_number');

            $table->string('primary_image')->nullable()->after('image');
            $table->string('thumbnail')->nullable()->after('primary_image');
            $table->json('gallery')->nullable()->after('thumbnail');
            $table->string('video_url')->nullable()->after('gallery');
            $table->string('youtube_url')->nullable()->after('video_url');

            $table->decimal('weight', 8, 3)->nullable()->after('youtube_url');
            $table->decimal('length', 8, 2)->nullable()->after('weight');
            $table->decimal('width', 8, 2)->nullable()->after('length');
            $table->decimal('height', 8, 2)->nullable()->after('width');
            $table->string('shipping_class')->nullable()->after('height');
            $table->boolean('free_shipping')->default(false)->after('shipping_class');
            $table->boolean('cod_available')->default(true)->after('free_shipping');

            $table->string('meta_title')->nullable()->after('cod_available');
            $table->text('meta_keywords')->nullable()->after('meta_title');
            $table->text('meta_description')->nullable()->after('meta_keywords');
            $table->string('canonical_url')->nullable()->after('meta_description');
            $table->string('og_image')->nullable()->after('canonical_url');

            $table->string('status')->default('draft')->after('og_image');
            $table->boolean('trending')->default(false)->after('featured');
            $table->boolean('new_arrival')->default(false)->after('trending');
            $table->boolean('best_seller')->default(false)->after('new_arrival');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->foreignId('color_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('size_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('weight', 8, 3)->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('feature');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('tag');
            $table->timestamps();
        });

        Schema::create('product_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title')->nullable();
            $table->string('file_path');
            $table->timestamps();
        });

        Schema::create('product_bike_model', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bike_model_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'bike_model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_bike_model');
        Schema::dropIfExists('product_documents');
        Schema::dropIfExists('product_tags');
        Schema::dropIfExists('product_features');
        Schema::dropIfExists('product_variants');

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sub_category_id');
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('manufacturer_id');
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropConstrainedForeignId('tax_id');
            $table->dropConstrainedForeignId('unit_id');
            $table->dropColumn([
                'short_name', 'sku', 'barcode', 'qr_code', 'product_type', 'product_condition',
                'hsn_code', 'country_of_origin', 'warranty', 'return_days', 'replace_days',
                'min_order_qty', 'max_order_qty', 'short_description', 'long_description',
                'specification', 'installation_guide', 'box_contents', 'care_instructions', 'warranty_info',
                'purchase_price', 'landing_cost', 'selling_price', 'mrp', 'discount_percent',
                'offer_price', 'dealer_price', 'wholesale_price', 'tax_included', 'commission',
                'reserved_stock', 'low_stock_alert', 'warehouse', 'rack_number', 'reorder_level',
                'primary_image', 'thumbnail', 'gallery', 'video_url', 'youtube_url',
                'weight', 'length', 'width', 'height', 'shipping_class', 'free_shipping', 'cod_available',
                'meta_title', 'meta_keywords', 'meta_description', 'canonical_url', 'og_image',
                'status', 'trending', 'new_arrival', 'best_seller',
            ]);
        });
    }
};
