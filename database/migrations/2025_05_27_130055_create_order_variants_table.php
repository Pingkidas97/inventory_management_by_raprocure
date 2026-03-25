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
        Schema::create('order_variants', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->nullable();
            $table->string('rfq_id')->nullable();
            
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('rfq_product_variant_id')->default(0);
            $table->unsignedBigInteger('rfq_quotation_variant_id')->default(0);

            $table->decimal('order_quantity', 15, 2)->nullable();
            $table->decimal('order_mrp', 25, 2)->nullable();
            $table->decimal('order_discount', 25, 2)->nullable();
            $table->decimal('order_price', 25, 2);

            $table->integer('product_hsn_code')->nullable();
            $table->integer('product_gst')->nullable();

            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_variant');
    }
};
