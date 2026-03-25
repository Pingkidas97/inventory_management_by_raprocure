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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name');
            $table->enum('type', ['1', '2'])->default(1)->comment('1->Buyer, 2->Vendor');
            $table->integer('no_of_user')->nullable()->default(0);
            $table->float('price', 20, 2);
            $table->integer('trial_period')->nullable();
            $table->enum('status', ['1', '2'])->default(1)->comment('1->Active, 2->Inactive');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
