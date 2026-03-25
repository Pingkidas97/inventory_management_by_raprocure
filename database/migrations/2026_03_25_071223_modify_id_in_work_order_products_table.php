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
            ALTER TABLE work_order_products
            MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ADD PRIMARY KEY (id)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE work_order_products
            MODIFY id BIGINT(20) UNSIGNED NOT NULL
        ");
    }
};
