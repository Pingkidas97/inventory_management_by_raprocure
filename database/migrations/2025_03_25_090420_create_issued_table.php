<?php

// database/migrations/YYYY_MM_DD_create_issued_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssuedTable extends Migration
{
    public function up()
    {
        Schema::create('issued', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('issued_no')->unique();
            $table->string('qty', 20)->nullable()->default('0');
            $table->float('consume_qty', 15, 2)->default(0.00);
            $table->unsignedBigInteger('issued_return_for')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->string('issued_to', 255)->nullable();
            $table->tinyInteger('inv_status')->default(1)->comment('1->Active, 2->In-active');
            $table->tinyInteger('consume')->default(2)->comment('2 => not consume, 1=> consume');
            $table->tinyInteger('is_deleted')->default(2);
            $table->unsignedBigInteger('updated_by');
            $table->dateTime('updated_at')->useCurrent();

            // Foreign Keys
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('issued');
    }
}

