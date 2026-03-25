<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('issued_returns', function (Blueprint $table) {
            $table->dropIndex('issued_returns_branch_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::table('issued_returns', function (Blueprint $table) {
             $table->index('branch_id', 'issued_returns_branch_id_foreign');
        });
    }
};
