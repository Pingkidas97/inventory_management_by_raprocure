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
        Schema::table('return_stocks', function (Blueprint $table) {
            $table->string('vendor_name', 255)
            ->after('qty')
            ->collation('utf8mb4_general_ci')
            ->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_stocks', function (Blueprint $table) {
            $table->dropColumn('vendor_name');
        });
    }
};
