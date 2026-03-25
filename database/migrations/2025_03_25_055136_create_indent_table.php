<?php

// database/migrations/YYYY_MM_DD_create_indent_mgt_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndentTable extends Migration
{
    public function up()
    {
        Schema::create('indent', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->tinyInteger('inv_status')->default(1)->comment('1->in-use, 2->not in use');
            $table->tinyInteger('is_active')->default(1)->comment('1->Approved, 2->Unapproved');
            $table->unsignedBigInteger('inventory_unique_id')->unique()->nullable();
            $table->string('indent_qty', 20)->nullable();
            $table->string('grn_qty', 20)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->tinyInteger('closed_indent')->default(2)->comment('1->closed, 2->open');
            $table->tinyInteger('is_deleted')->nullable()->default(2)->comment('1->Deleted');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('updated_at')->useCurrent();

            // Foreign Keys
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('indent_mgt');
    }
}
