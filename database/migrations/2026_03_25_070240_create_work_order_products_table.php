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
        Schema::create('work_order_products', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('work_order_id');
            $table->unsignedBigInteger('inventory_id')->default(0);
            $table->unsignedBigInteger('product_id')->default(0);
            $table->decimal('product_quantity', 10, 3)->nullable();
            $table->decimal('product_price', 25, 2)->nullable();
            $table->decimal('product_mrp', 25, 2)->nullable();
            $table->decimal('product_disc', 5, 2)->nullable();
            $table->decimal('product_total_amount', 50, 2)->nullable();
            $table->integer('product_gst')->nullable();

            // Optional: add foreign key constraint
            // $table->foreign('work_order_id')->references('id')->on('work_orders')->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_products');
    }
};
