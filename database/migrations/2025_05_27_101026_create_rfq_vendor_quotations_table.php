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
        Schema::create('rfq_vendor_quotations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rfq_id', 100);
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('rfq_product_variant_id')->nullable();
            $table->decimal('price', 25, 2)->nullable();
            $table->decimal('mrp', 25, 2)->default(0.00);
            $table->decimal('discount', 25, 2)->default(0.00);
            $table->decimal('buyer_price', 25, 2)->default(0.00);
            $table->string('specification', 3000)->nullable();
            $table->string('vendor_attachment_file', 255)->nullable();
            $table->string('vendor_brand', 2000)->nullable();
            $table->string('vendor_remarks', 3000)->nullable();
            $table->string('vendor_additional_remarks', 3000)->nullable();
            $table->string('vendor_price_basis', 2000)->nullable();
            $table->string('vendor_payment_terms', 2000)->nullable();
            $table->integer('vendor_delivery_period')->nullable();
            $table->integer('vendor_price_validity')->nullable();
            $table->integer('vendor_dispatch_branch')->nullable();
            $table->string('vendor_currency', 60)->nullable();
            $table->unsignedBigInteger('buyer_user_id')->nullable();
            $table->unsignedBigInteger('vendor_user_id');
            $table->enum('status', ['1', '2'])->default('1')->comment('1- sent price to buyer, 2- Save price by vendor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_vendor_quotations');
    }
};
