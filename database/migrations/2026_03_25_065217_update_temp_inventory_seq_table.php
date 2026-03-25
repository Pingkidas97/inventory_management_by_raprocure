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
        DB::statement("
            UPDATE inventories i
            JOIN temp_inventory_seq t 
                ON i.id = t.id
            SET i.item_code = CONCAT(
                t.prefix,
                '-',
                LPAD(t.seq,4,'0')
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('inventories')->update(['item_code' => null]);
    }
};
