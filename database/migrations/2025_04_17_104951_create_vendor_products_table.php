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
        Schema::create('vendor_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('vendor_id')->comment('Company id of Vendor');
            $table->unsignedBigInteger('product_id')->comment('Master product id');
            $table->string('product_name')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('dealer_type_id');
            $table->unsignedInteger('uom')->nullable();
            $table->unsignedInteger('gst_id');
            $table->unsignedInteger('hsn_code')->nullable();
            $table->string('catalogue')->nullable();
            $table->string('specification', 5000)->nullable();
            $table->string('specification_file')->nullable();
            $table->string('size', 1500)->nullable();
            $table->string('certificates', 3000)->nullable();
            $table->string('certificates_file')->nullable();
            $table->string('dealership', 2000)->nullable();
            $table->string('dealership_file')->nullable();
            $table->string('packaging', 2000)->nullable();
            $table->string('model_no')->nullable();
            $table->string('gorw')->nullable();
            $table->integer('gorw_year')->nullable();
            $table->integer('gorw_month')->nullable();
            $table->string('brand')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->timestamp('request_received_date')->nullable()->comment('Request received date');
            $table->unsignedBigInteger('verified_by')->nullable()->comment('Super admin user id');
            $table->enum('vendor_status', ['1', '2', '3', '4'])->default('1')->comment('1=Active, 2=Inactive, 3=Inactive by SA, 4=Hide by SA');
            $table->tinyInteger('edit_status')->comment('0=Verified,1=Edit,2=New Product,3=Product for Approval');
            $table->tinyInteger('approval_status')->comment('1=Approved, 2=Query Raised, 3=Re-submitted, 4=Product for Approval');
            $table->unsignedBigInteger('added_by_user_id')->comment('Person who added, could be SA');
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_products');
    }
};
