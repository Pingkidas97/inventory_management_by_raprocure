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
        Schema::create('inventories', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('inventory_unique_id')->nullable();
            $table->unsignedBigInteger('buyer_parent_id')->default(0)->comment('company id');
            $table->unsignedBigInteger('buyer_branch_id')->default(0)->comment('buyer branch id');
            $table->unsignedBigInteger('product_id')->default(0)->comment('product id');

            $table->string('product_name', 255)->nullable();
            $table->string('buyer_product_name', 255)->nullable();
            $table->string('specification', 3000)->nullable();
            $table->string('size', 1500)->nullable();
            $table->string('opening_stock', 20)->nullable();

            $table->double('stock_price')->default(0);
            $table->integer('uom_id')->default(0);

            $table->string('inventory_grouping', 255)->nullable();
            $table->tinyInteger('inventory_type_id')->default(0);

            $table->string('indent_min_qty', 20)->nullable();
            $table->string('product_brand', 255)->nullable();

            $table->tinyInteger('is_indent')->default(2)->comment('1=>use, 2=>not use');

            $table->unsignedBigInteger('created_by')->default(0);
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
