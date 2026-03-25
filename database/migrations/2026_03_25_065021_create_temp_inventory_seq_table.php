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
            CREATE TEMPORARY TABLE temp_inventory_seq AS
            SELECT 
                i.id,
                CONCAT(
                    UPPER(LEFT(b.organisation_short_code,2)),
                    UPPER(LEFT(bd.name,2))
                ) AS prefix,
                ROW_NUMBER() OVER (
                    PARTITION BY i.buyer_parent_id, i.buyer_branch_id
                    ORDER BY i.id
                ) AS seq
            FROM inventories i
            JOIN buyers b 
                ON i.buyer_parent_id = b.user_id
            JOIN branch_details bd 
                ON i.buyer_branch_id = bd.branch_id
                AND bd.record_type = 1
                AND bd.user_type = 1
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       DB::statement("DROP TEMPORARY TABLE IF EXISTS temp_inventory_seq");
    }
};
