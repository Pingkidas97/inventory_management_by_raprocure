<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_stocks', function (Blueprint $table) {
            $table->dropForeign('return_stocks_branch_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::table('return_stocks', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }
};
