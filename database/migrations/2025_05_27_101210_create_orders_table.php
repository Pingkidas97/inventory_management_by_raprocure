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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rfq_id', 255)->nullable();
            $table->unsignedBigInteger('vendor_id');
            $table->string('po_number', 255)->nullable();
            $table->string('buyer_order_number', 255)->nullable();
            $table->decimal('order_total_amount', 15, 2)->nullable();
            $table->enum('order_status', ['1', '2', '3'])->default('1')->comment('1->Order Confirmed, 2-> Order Cancelled, 3-> Order to Approve');
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('buyer_user_id');
            $table->unsignedBigInteger('unapprove_by_user_id')->nullable();
            $table->string('order_price_basis', 2000)->nullable();
            $table->string('order_payment_term', 2000)->nullable();
            $table->integer('order_delivery_period')->nullable();
            $table->string('order_remarks', 3000)->nullable();
            $table->string('order_add_remarks', 3000)->nullable();
            $table->string('vendor_currency', 60)->default('₹');
            $table->enum('int_buyer_vendor', ['1', '2'])->comment('1-> International Buyer or Vendor, 2-> Indian Buyer and Vendor.');
            $table->integer('api_order_status')->nullable();
            $table->string('api_order_no', 255)->nullable();
            $table->string('api_order_response', 3000)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
