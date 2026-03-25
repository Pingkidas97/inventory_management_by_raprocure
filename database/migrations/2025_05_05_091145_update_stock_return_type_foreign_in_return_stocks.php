<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_stocks', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['stock_return_type']);

            // Add the new foreign key constraint
            $table->foreign('stock_return_type')
                ->references('id')
                ->on('issued_types')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('return_stocks', function (Blueprint $table) {
            // Rollback: drop the updated foreign key
            $table->dropForeign(['stock_return_type']);

            // Restore the old foreign key
            $table->foreign('stock_return_type')
                ->references('id')
                ->on('stock_return_type')
                ->onDelete('cascade');
        });
    }
};
