<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_stocks', function (Blueprint $table) {
            $table->dropUnique(['stock_no']); // remove unique index from stock_no
        });
    }

    public function down(): void
    {
        Schema::table('return_stocks', function (Blueprint $table) {
            $table->unique('stock_no'); // add it back in case of rollback
        });
    }
};
