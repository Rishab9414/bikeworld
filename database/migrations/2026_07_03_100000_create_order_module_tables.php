<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('discount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('coupon_discount', 12, 2)->default(0)->after('discount');
            $table->decimal('wallet_discount', 12, 2)->default(0)->after('coupon_discount');
            $table->decimal('shipping_charge', 12, 2)->default(0)->after('wallet_discount');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('shipping_charge');
            $table->decimal('grand_total', 12, 2)->nullable()->after('tax_amount');
            $table->string('payment_method')->default('cod')->after('grand_total');
            $table->string('payment_status')->default('pending')->after('payment_method');
            $table->date('expected_delivery')->nullable()->after('payment_status');
            $table->unsignedBigInteger('invoice_id')->nullable()->after('expected_delivery');
            $table->unsignedBigInteger('shipment_id')->nullable()->after('invoice_id');
            $table->json('shipping_address_json')->nullable()->after('billing_address');
            $table->json('billing_address_json')->nullable()->after('shipping_address_json');
            $table->boolean('stock_reserved')->default(false)->after('notes');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            $table->string('sku')->nullable()->after('product_name');
            $table->decimal('discount', 12, 2)->default(0)->after('price');
            $table->decimal('gst', 12, 2)->default(0)->after('discount');
            $table->decimal('total', 12, 2)->nullable()->after('gst');
            $table->decimal('weight', 8, 3)->nullable()->after('total');
            $table->string('status')->default('pending')->after('weight');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_pdf')->nullable();
            $table->date('invoice_date');
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('courier_name')->default('Delhivery');
            $table->string('shipment_id')->nullable()->index();
            $table->string('waybill')->nullable()->index();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('pickup_request_id')->nullable();
            $table->string('shipping_label')->nullable();
            $table->string('manifest')->nullable();
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->date('estimated_delivery')->nullable();
            $table->date('pickup_date')->nullable();
            $table->string('shipment_status')->default('pending');
            $table->timestamps();
        });

        Schema::create('shipment_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->string('location')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('scan_time')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->string('title');
            $table->text('remarks')->nullable();
            $table->string('actor')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->foreign('shipment_id')->references('id')->on('shipments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['shipment_id']);
            $table->dropColumn([
                'discount', 'coupon_discount', 'wallet_discount', 'shipping_charge',
                'tax_amount', 'grand_total', 'payment_method', 'payment_status',
                'expected_delivery', 'invoice_id', 'shipment_id',
                'shipping_address_json', 'billing_address_json', 'stock_reserved',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variant_id');
            $table->dropColumn(['sku', 'discount', 'gst', 'total', 'weight', 'status']);
        });

        Schema::dropIfExists('order_status_logs');
        Schema::dropIfExists('shipment_tracking');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('invoices');
    }
};
