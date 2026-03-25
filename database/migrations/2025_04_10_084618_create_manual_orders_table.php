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
        Schema::create('manual_orders', function (Blueprint $table) {
            $table->id();
            $table->string('manual_po_number')->nullable();
            $table->unsignedBigInteger('vendor_id')->default(0);
            $table->unsignedBigInteger('buyer_id')->default(0);
            $table->unsignedBigInteger('buyer_user_id')->default(0);
            $table->integer('order_status')->default(1)->comment('1-use, 2->not use');
            $table->string('order_price_basis', 2000)->nullable();
            $table->string('order_payment_term', 2000)->nullable();
            $table->integer('order_delivery_period')->nullable();
            $table->string('order_remarks', 3000)->nullable();
            $table->string('order_add_remarks', 3000)->nullable();
            $table->unsignedBigInteger('prepared_by')->default(0);
            $table->unsignedBigInteger('approved_by')->default(0);
            $table->timestamps();
            // Foreign Key
            $table->foreign('prepared_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_orders');
    }
};
