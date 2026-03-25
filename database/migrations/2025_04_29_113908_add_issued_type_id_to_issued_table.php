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
        Schema::table('issued', function (Blueprint $table) {
            $table->unsignedBigInteger('issued_type')->nullable()->after('issued_to');
            $table->foreign('issued_type')->references('id')->on('issued_types')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issued', function (Blueprint $table) {
            $table->dropForeign(['issued_type']);
            $table->dropColumn('issued_type');
        });
    }
};
