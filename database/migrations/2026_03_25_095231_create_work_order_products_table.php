<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_products', function (Blueprint $table) {
            $table->id(); // bigint unsigned AUTO_INCREMENT

            $table->integer('work_order_id');

            $table->string('product_description', 5000);

            $table->decimal('product_quantity', 10, 3)->nullable();
            $table->decimal('product_price', 25, 2)->nullable();
            $table->decimal('product_mrp', 25, 2)->nullable();
            $table->decimal('product_disc', 5, 2)->nullable();
            $table->decimal('product_total_amount', 50, 2)->nullable();

            $table->integer('product_gst')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_products');
    }
};