<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->string('product_name_snapshot')->nullable();
            $table->string('barcode_snapshot')->nullable();
            $table->string('unit_snapshot')->nullable();
        });

        Schema::create('supplier_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->string('channel');
            $table->text('message');
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_communications');

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn([
                'product_name_snapshot',
                'barcode_snapshot',
                'unit_snapshot',
            ]);
        });
    }
};
