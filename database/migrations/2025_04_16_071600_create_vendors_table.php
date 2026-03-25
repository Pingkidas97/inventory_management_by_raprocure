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
        Schema::create('vendors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->string('vendor_code')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('profile_img')->nullable();
            $table->date('date_of_incorporation')->nullable();
            $table->integer('nature_of_organization')->nullable();
            $table->integer('nature_of_business')->nullable();
            $table->string('other_contact_details')->nullable();
            $table->string('registered_address', 3000)->nullable();
            $table->integer('country')->nullable();
            $table->integer('state')->nullable();
            $table->integer('city')->nullable();
            $table->string('pincode', 100)->nullable();
            $table->string('gstin')->nullable();
            $table->string('gstin_document')->nullable();
            $table->string('company_name1')->nullable();
            $table->string('company_name2')->nullable();
            $table->string('registered_product_name', 5000)->nullable();
            $table->string('website')->nullable();
            $table->string('msme')->nullable();
            $table->string('msme_certificate')->nullable();
            $table->string('iso_registration')->nullable();
            $table->string('iso_regi_certificate')->nullable();
            $table->string('description', 3000)->nullable();
            $table->tinyInteger('t_n_c')->nullable()->comment('1->terms and condition');
            $table->string('referred_by', 500)->nullable();
            $table->bigInteger('assigned_manager')->nullable();
            $table->integer('plan_id')->nullable()->comment('Plan id on profile verification');
            $table->bigInteger('updated_by')->nullable();
            $table->string('group_id',50)->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
