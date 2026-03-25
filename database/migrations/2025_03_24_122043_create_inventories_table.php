<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->integer('inventory_unique_id')->unique()->nullable();
            $table->unsignedBigInteger('buyer_parent_id')->nullable()->default(0);
            $table->unsignedBigInteger('buyer_branch_id')->nullable()->default(0);
            $table->unsignedBigInteger('product_id')->nullable()->default(0);
            $table->string('product_name', 100)->nullable();
            $table->string('buyer_product_name', 100)->nullable();
            $table->string('specification', 3000)->nullable();
            $table->string('size', 1500)->nullable();
            $table->string('opening_stock', 20)->nullable();
            $table->double('stock_price')->default(0);
            $table->unsignedBigInteger('uom_id')->default(0);
            $table->string('inventory_grouping', 255)->nullable();
            $table->unsignedBigInteger('inventory_type_id')->nullable()->default(0); // Fixed type
            $table->string('indent_min_qty', 20)->nullable();
            $table->string('product_brand', 255)->nullable();
            $table->tinyInteger('is_indent')->default(2)->comment('1->use, 2->not use');
            $table->unsignedBigInteger('created_by')->nullable()->default(0);
            $table->unsignedBigInteger('updated_by')->nullable()->default(0);
            $table->timestamps(); // Replaces created_at & updated_at

            // Foreign Keys
            $table->foreign('buyer_parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('uom_id')->references('id')->on('uoms')->onDelete('cascade');
            $table->foreign('inventory_type_id')->references('id')->on('inventory_type')->onDelete('cascade'); // Fixed FK
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories');
    }
}
