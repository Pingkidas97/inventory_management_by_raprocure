<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('manual_order_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manual_order_id');
            $table->unsignedBigInteger('inventory_id')->default(0);
            $table->unsignedBigInteger('product_id')->default(0);
            $table->float('product_quantity', 20, 2)->nullable();
            $table->decimal('product_price', 25, 2)->nullable();
            $table->decimal('product_total_amount', 50, 2)->nullable();
            $table->integer('product_gst')->nullable();
            $table->timestamps();

            // Foreign Key
            $table->foreign('manual_order_id')->references('id')->on('manual_orders')->onDelete('cascade');
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_order_products');
    }
};
