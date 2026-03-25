<?php

// database/migrations/YYYY_MM_DD_create_stock_return_type_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockReturnTypeTable extends Migration
{
    public function up()
    {
        Schema::create('stock_return_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('status', ['1', '2'])->default('1')->comment('1=>Active, 2=>Inactive');
            $table->dateTime('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_return_type');
    }
}

