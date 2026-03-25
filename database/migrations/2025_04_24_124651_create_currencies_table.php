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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id(); // AUTO_INCREMENT primary key
            $table->string('currency_name', 100);
            $table->string('currency_symbol', 20);
            $table->enum('status', ['1', '2'])->default('1')->comment('1=>Active,2=>Inactive');
            $table->timestamp('created_at')->useCurrent();
            $table->bigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->bigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
