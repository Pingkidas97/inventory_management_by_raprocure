<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id(); // bigint unsigned AUTO_INCREMENT

            $table->string('work_order_number')->nullable();

            $table->unsignedBigInteger('vendor_id')->default(0);
            $table->unsignedBigInteger('buyer_id')->default(0);
            $table->unsignedBigInteger('buyer_user_id')->default(0);

            $table->integer('branch_id');
            $table->integer('currency_id');

            $table->integer('order_status')
                  ->default(1)
                  ->comment('1 => Order Generated, 2 => Order Cancelled');

            $table->string('order_price_basis', 2000)->nullable();
            $table->string('order_payment_term', 2000)->nullable();

            $table->integer('order_delivery_period')->nullable();

            $table->string('order_remarks', 3000)->nullable();
            $table->string('order_add_remarks', 3000)->nullable();

            $table->unsignedBigInteger('prepared_by')->default(0);
            $table->unsignedBigInteger('approved_by')->default(0);

            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};