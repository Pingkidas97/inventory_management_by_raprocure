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
        Schema::create('force_closures', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->unsignedBigInteger('inventory_id');
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('rfq_product_variant_id')->nullable();
            $table->string('rfq_number', 100)->nullable();
            $table->decimal('original_rfq_quantity', 10, 3)->default(0);
            $table->decimal('updated_rfq_quantity', 10, 3)->default(0);
            $table->decimal('total_order_quantity', 10, 3)->default(0);
            $table->decimal('total_grn_quantity', 10, 3)->default(0);
            $table->unsignedBigInteger('buyer_parent_id');
            $table->unsignedBigInteger('buyer_id');
            $table->timestamps(); // created_at + updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('force_closures');
    }
};
