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
        Schema::create('rfqs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rfq_id', 100);
            $table->bigInteger('buyer_id')->nullable();
            $table->bigInteger('buyer_user_id')->nullable();
            $table->tinyInteger('record_type')->comment("1->Draft, 2->RFQ, 3-Edit");
            $table->string('prn_no')->nullable();
            $table->integer('buyer_branch')->nullable();
            $table->date('last_response_date')->nullable();
            $table->string('buyer_price_basis', 2000)->nullable();
            $table->string('buyer_pay_term', 2000)->nullable();
            $table->integer('buyer_delivery_period')->nullable();
            $table->string('warranty_gurarantee')->nullable();
            $table->bigInteger('edit_by')->nullable();
            $table->string('edit_rfq_id', 100)->nullable();
            $table->date('scheduled_date')->nullable();
            $table->tinyInteger('is_bulk_rfq')->default(2)->comment("1->Yes,2->No");
            $table->integer('buyer_rfq_status')->comment(
                "1-> RFQ Generated, 2-> Scheduled RFQ, 3-> Active RFQ, 4-> Counter Offer Sent, 5-> Order Confirmed, 
                6-> Counter Offer Received, 7-> Quotation Received, 8-> Closed RFQ, 9->Partial Order, 10->Closed with Partial Order"
            )->nullable();
            $table->tinyInteger('buyer_rfq_read_status')->default(2)->comment("1->Unread, 2->Read");
            $table->integer('inventory_id')->nullable();
            $table->tinyInteger('inventory_status')->default(1)->comment("1->in use, 0->not in use");
            $table->integer('api_id')->comment("Buyer API Primary ID")->nullable();
            $table->tinyInteger('is_api_request')->default(0);
            $table->tinyInteger('api_order_status')->default(0);
            $table->tinyInteger('is_used')->default(1);
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfqs');
    }
};
