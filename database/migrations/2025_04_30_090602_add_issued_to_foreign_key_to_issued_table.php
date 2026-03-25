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
        try {
            Schema::table('issued', function (Blueprint $table) {
                $table->dropForeign(['issued_to']);
            });
        } catch (\Throwable $e) {
        }
        if (Schema::hasColumn('issued', 'issued_to')) {
            Schema::table('issued', function (Blueprint $table) {
                $table->dropColumn('issued_to');
            });
        }
        Schema::table('issued', function (Blueprint $table) {
            $table->unsignedBigInteger('issued_to')->nullable()->after('remarks');
        });
        Schema::table('issued', function (Blueprint $table) {
            $table->foreign('issued_to')
                  ->references('id')
                  ->on('issue_to')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issued', function (Blueprint $table) {
            try {
                $table->dropForeign(['issued_to']);
            } catch (\Throwable $e) {
                // ignore if foreign key doesn't exist
            }

            if (Schema::hasColumn('issued', 'issued_to')) {
                $table->dropColumn('issued_to');
            }
        });
    }
};
