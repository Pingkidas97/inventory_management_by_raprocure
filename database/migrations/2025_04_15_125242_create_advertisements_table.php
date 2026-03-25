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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('types')->nullable();
            $table->string('buyer_vendor_name')->nullable();
            $table->date('received_on')->nullable();
            $table->date('payment_received_on')->nullable();
            $table->date('validity_period_from')->nullable();
            $table->date('validity_period_to')->nullable();
            $table->string('images')->nullable(); 
            $table->string('ads_url')->nullable();
            $table->tinyInteger('ad_position')->nullable();
            $table->enum('status', ['1', '2'])->default(1); // 1 = Active, 2 = Inactive
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
