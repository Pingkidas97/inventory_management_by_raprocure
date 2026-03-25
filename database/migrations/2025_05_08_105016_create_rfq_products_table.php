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
        Schema::create('rfq_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rfq_id', 100);
            $table->bigInteger('product_id')->comment("maps to master product id");
            $table->string('brand')->nullable();
            $table->string('remarks', 3000)->nullable();
            $table->integer('product_order')->nullable();
            $table->string('edit_rfq_id', 100)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_products');
    }
};
