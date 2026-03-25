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
        Schema::table('manual_order_products', function (Blueprint $table) {
            $table->decimal('product_quantity', 20, 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_order_products', function (Blueprint $table) {
            $table->decimal('product_quantity', 20, 2)->change();
        });
    }
};
