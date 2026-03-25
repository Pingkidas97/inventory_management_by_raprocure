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
        Schema::create('user_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('user_type', ['1', '2'])->default(1)->comment('1->Buyer, 2->Vendor');
            $table->bigInteger('user_id')->nullable();
            $table->integer('plan_id')->nullable();
            $table->string('plan_name')->nullable();
            $table->float('plan_amount', 20, 2)->nullable();
            $table->integer('trial_period')->nullable()->default(0);
            $table->integer('no_of_users')->nullable();
            $table->float('discount', 20, 2)->nullable()->comment('in %');
            $table->float('gst', 8, 2)->nullable()->comment('in %');
            $table->float('final_amount', 20, 2)->nullable();
            $table->date('start_date')->nullable()->comment('subscription start date');
            $table->string('subscription_period')->nullable();
            $table->date('next_renewal_date')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('transaction_no')->nullable();
            $table->enum('is_expired', ['1', '2'])->default(2)->comment('1=>Expired, 2=>Not Expired');
            $table->string('payment_salt')->nullable();
            $table->string('extend_month')->nullable();
            $table->string('old_renewal_date', 1000)->nullable();            
            $table->bigInteger('activated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_plans');
    }
};
