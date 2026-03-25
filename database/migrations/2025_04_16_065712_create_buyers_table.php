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
        Schema::create('buyers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->nullable();
            $table->string('buyer_code')->nullable();
            $table->string('legal_name')->nullable();
            $table->date('incorporation_date')->nullable();
            $table->string('registered_address', 3000)->nullable();
            $table->integer('country')->nullable();
            $table->integer('state')->nullable();
            $table->integer('city')->nullable();
            $table->string('pincode', 11)->nullable();
            $table->string('gstin')->nullable();
            $table->string('pan')->nullable();
            $table->string('pan_file')->nullable();
            $table->string('website')->nullable();
            $table->string('product_details', 2000)->nullable();
            $table->string('organisation_description', 5000)->nullable();
            $table->string('organisation_short_code', 20)->nullable();
            $table->string('buyer_accept_tnc')->nullable();
            $table->integer('tab1_status')->nullable()->default('2')->comment('1->Yes, 2->No');
            $table->integer('tab2_status')->nullable()->default('2')->comment('1->Yes, 2->No');
            $table->integer('tab3_status')->nullable()->default('2')->comment('1->Yes, 2->No');
            $table->integer('tab4_status')->nullable()->default('2')->comment('1->Yes, 2->No');
            $table->bigInteger('assigned_manager')->nullable();
            $table->integer('rfq_number')->default('0')->comment('Used to generate rfq number for current buyer');
            $table->integer('plan_id')->nullable()->comment('Plan id on profile verification');
            $table->tinyInteger('t_n_c')->nullable()->comment('1->terms and condition');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyers');
    }
};
