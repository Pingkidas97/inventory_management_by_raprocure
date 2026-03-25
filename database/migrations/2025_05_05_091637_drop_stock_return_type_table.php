<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('stock_return_type');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('stock_return_type', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
};
