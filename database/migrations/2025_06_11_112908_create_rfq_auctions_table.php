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
        Schema::create('rfq_auctions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rfq_no', 100);
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('buyer_user_id');
            $table->string('auction_date', 20);
            $table->time('auction_start_time');
            $table->time('auction_end_time');
            $table->float('min_bid_decrement');
            $table->string('currency', 20)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_auctions');
    }
};
