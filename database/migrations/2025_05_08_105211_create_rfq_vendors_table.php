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
        Schema::create('rfq_vendors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rfq_id', 100);
            $table->bigInteger('vendor_user_id')->nullable();
            $table->integer('vendor_status')->comment(
                "1-> RFQ Generated, 2-> Scheduled RFQ, 3-> Active RFQ, 4-> Counter Offer Sent, 5-> Order Confirmed, 
                6-> Counter Offer Received, 7-> Quotation Received, 8-> Closed RFQ, 9->Partial Order, 10->Closed with Partial Order"
            );
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_vendors');
    }
};
