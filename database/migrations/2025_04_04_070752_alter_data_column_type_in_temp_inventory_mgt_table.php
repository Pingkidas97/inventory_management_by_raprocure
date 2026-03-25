<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDataColumnTypeInTempInventoryMgtTable extends Migration
{
    public function up()
    {
        Schema::table('temp_inventory_mgt', function (Blueprint $table) {
            $table->json('data')->change(); //  Change to JSON
        });
    }

    public function down()
    {
        Schema::table('temp_inventory_mgt', function (Blueprint $table) {
            $table->longText('data')->change(); // Revert if needed
        });
    }
}

