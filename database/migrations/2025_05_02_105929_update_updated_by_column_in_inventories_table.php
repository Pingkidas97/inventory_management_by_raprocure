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
        // Drop existing foreign key
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
        });

        // Change column to nullable and remove default 0
        Schema::table('inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->nullable()->default(null)->change();
        });

        // Re-add foreign key
        Schema::table('inventories', function (Blueprint $table) {
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop modified foreign key
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
        });

        // Revert to nullable with default 0
        Schema::table('inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->nullable()->default(0)->change();
        });

        // Re-add original foreign key
        Schema::table('inventories', function (Blueprint $table) {
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
