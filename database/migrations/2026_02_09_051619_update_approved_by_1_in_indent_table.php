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
        Schema::table('indent', function (Blueprint $table) {
            $table->bigInteger('approved_by_1')->nullable()->after('updated_by');
            $table->bigInteger('approved_by_2')->nullable()->after('approved_by_1');
        });

        DB::table('indent')
            ->whereNull('approved_by_1')
            ->whereNotNull('updated_by')
            ->update([
                'approved_by_1' => DB::raw('updated_by')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indent', function (Blueprint $table) {
            $table->dropColumn(['approved_by_1', 'approved_by_2']);
        });
    }
};
