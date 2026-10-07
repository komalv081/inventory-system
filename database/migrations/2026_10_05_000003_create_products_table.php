<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('barcode')->nullable();
            $table->string('sku')->nullable();
            $table->string('product_name');
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->text('description')->nullable();
            $table->string('pack_size')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('mrp', 12, 2)->nullable();
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->unsignedInteger('current_quantity')->default(0);
            $table->unsignedInteger('minimum_stock_threshold')->default(10);
            $table->string('product_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index('barcode');
            $table->index(['shop_id', 'product_name']);
            $table->index(['shop_id', 'barcode'], 'shop_barcode_unique');
            $table->index(['shop_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
