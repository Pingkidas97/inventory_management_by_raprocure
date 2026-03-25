<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIssuedReturnsTable extends Migration
{
    public function up()
    {
        Schema::table('issued_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('issued_return_type')->nullable()->change();
            $table->foreign('issued_return_type')
                ->references('id')->on('issued_types')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('issued_returns', function (Blueprint $table) {
            $table->dropForeign(['issued_return_type']);
            $table->unsignedBigInteger('issued_return_type')->default(0)->change();
        });
    }
}
