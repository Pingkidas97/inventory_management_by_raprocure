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
        Schema::create('rfq_product_variants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rfq_id', 100);
            $table->bigInteger('product_id')->comment("maps to master product id");
            $table->string('specification', 4000)->nullable();
            $table->string('size', 1500)->nullable();
            $table->float('quantity', 15, 2)->nullable();
            $table->integer('uom')->nullable();
            $table->string('attachment')->nullable();
            $table->integer('variant_order')->nullable();
            $table->bigInteger('variant_grp_id');
            $table->bigInteger('edit_id')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_product_variants');
    }
};
