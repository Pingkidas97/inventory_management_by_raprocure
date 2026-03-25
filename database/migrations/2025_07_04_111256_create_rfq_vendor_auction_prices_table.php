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
        Schema::create('rfq_vendor_auction_price', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rfq_no', 100);
            $table->unsignedBigInteger('rfq_auction_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('rfq_product_veriant_id');
            $table->decimal('vend_price', 25, 2);

            $table->string('vend_specs', 10000)->nullable();
            $table->string('vend_price_basis', 2000)->nullable();
            $table->string('vend_payment_terms', 2000)->nullable();
            $table->integer('vend_delivery_period')->nullable();
            $table->float('vend_price_validity')->nullable();
            $table->integer('vend_dispatch_branch')->nullable();
            $table->string('vend_currency', 20)->nullable();
            $table->unsignedBigInteger('vendor_user_id')->nullable();

            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_vendor_auction_price');
    }
};
