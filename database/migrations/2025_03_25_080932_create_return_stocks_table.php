<?php

// database/migrations/YYYY_MM_DD_create_return_stocks_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnStocksTable extends Migration
{
    public function up()
    {
        Schema::create('return_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->integer('stock_no')->unique();
            $table->decimal('qty', 20, 2);
            $table->string('remarks', 255)->nullable();
            $table->unsignedBigInteger('stock_return_for')->nullable()->comment('grn id, 0->opening stock');
            $table->string('stock_vendor_name', 255)->nullable();
            $table->string('stock_vehicle_no_lr_no', 50)->nullable();
            $table->string('stock_debit_note_no', 20)->nullable();
            $table->string('stock_frieght', 20)->nullable();
            $table->unsignedBigInteger('stock_return_type')->nullable();
            $table->tinyInteger('is_deleted')->default(2)->comment('2->not deleted, 1->deleted');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('updated_at')->useCurrent();

            // Foreign Keys
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branch_details')->onDelete('cascade');
            $table->foreign('stock_return_type')->references('id')->on('stock_return_type')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('return_stocks');
    }
}
