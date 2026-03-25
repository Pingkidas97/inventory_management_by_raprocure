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
        DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_inventory_seq");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
