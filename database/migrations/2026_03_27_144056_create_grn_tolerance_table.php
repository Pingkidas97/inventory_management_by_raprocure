<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grn_tolerance', function (Blueprint $table) {
            $table->id(); // auto increment

            $table->unsignedBigInteger('buyer_id');
            $table->integer('tolerance');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
       
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_tolerance');
    }
};
