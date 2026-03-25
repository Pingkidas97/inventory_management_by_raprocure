<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrnsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grns', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id')->nullable(); // from tbl_order_confirmation_details
            $table->string('po_number')->nullable();

            $table->unsignedBigInteger('company_id')->nullable()->default(null); // FK to users.id
            $table->unsignedBigInteger('stock_id')->nullable()->default(null);   // FK to return_stocks.id
            $table->integer('stock_return_for')->nullable()->default(0);

            $table->integer('grn_no')->nullable()->default(0);
            $table->string('grn_qty', 20)->nullable()->default('0');

            $table->unsignedBigInteger('inventory_id'); // FK to inventories.id

            $table->tinyInteger('inv_status')->default(1); // 1=in-use, 2=not in use

            $table->string('approved_by')->nullable();
            $table->string('order_no')->nullable();
            $table->string('order_qty', 20)->nullable();
            $table->double('rate', 10, 2)->nullable();

            $table->float('grn_buyer_rate', 15, 2)->default(0.00);
            $table->string('rfq_no')->nullable();

            $table->tinyInteger('grn_type')->default(1); // 1=with order, 2=without order
            $table->string('vendor_name')->nullable();
            $table->string('vendor_invoice_number', 50)->nullable();
            $table->string('vehicle_no_lr_no', 20)->nullable();
            $table->string('gross_wt', 20)->nullable();
            $table->string('gst_no', 20)->nullable();
            $table->string('frieght_other_charges', 20)->nullable();

            $table->boolean('is_deleted')->default(0);

            $table->unsignedBigInteger('updated_by')->nullable()->default(null); // FK to users.id
            $table->timestamp('updated_date')->useCurrent();

            $table->timestamps();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('stock_id')->references('id')->on('return_stocks')->onDelete('set null');
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grns');
    }
}

