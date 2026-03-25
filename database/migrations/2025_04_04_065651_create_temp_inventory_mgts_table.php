<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempInventoryMgtsTable extends Migration
{
    public function up()
    {
        Schema::create('temp_inventory_mgt', function (Blueprint $table) {
            $table->id();
            $table->integer('srno');
            $table->tinyInteger('is_verify')->default(2);
            $table->unsignedBigInteger('user_id');
            $table->string('action');
            $table->json('data'); //  JSON column instead of longText
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('temp_inventory_mgt');
    }
}
