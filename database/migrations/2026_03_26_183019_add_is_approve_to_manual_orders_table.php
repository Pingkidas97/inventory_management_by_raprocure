<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up()
    {
        Schema::table('manual_orders', function (Blueprint $table) {
            $table->integer('is_approve')
                  ->default(1)
                  ->after('approved_by');
        });
    }

    public function down()
    {
        Schema::table('manual_orders', function (Blueprint $table) {
            $table->dropColumn('is_approve');
        });
    }
};
