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
        Schema::create('branch_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('branch_id');
            $table->enum('user_type', ['1', '2'])->default('1')->comment('1->Buyer, 2->Vendor');
            $table->bigInteger('user_id')->nullable();
            $table->enum('record_type', ['1', '2'])->default('1')->comment('1->Branch Details, 2->Top Management');
            $table->string('name')->nullable();
            $table->string('address', 2000)->nullable();
            $table->integer('country')->nullable();
            $table->integer('state')->nullable();
            $table->integer('city')->nullable();
            $table->string('pincode', 20)->nullable();
            $table->string('gstin')->nullable();
            $table->string('gstin_file')->nullable();
            $table->string('authorized_name')->nullable();
            $table->string('authorized_designation')->nullable();
            $table->string('mobile', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('output_details', 2000)->nullable();
            $table->string('installed_capacity')->nullable();
            $table->string('categories')->nullable();
            $table->integer('top_management_designation')->nullable();
            $table->enum('status', ['1', '2'])->default('1')->comment('1->Active, 2->Inactive');
            $table->enum('is_regd_address', ['1', '2'])->default('2')->comment('USED FOR VENDORS ONLY: 1->Regd. Address, 2->Branch Address');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_details');
    }
};
