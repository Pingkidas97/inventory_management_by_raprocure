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
        Schema::create('temp_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->integer('country_code')->nullable();
            $table->string('mobile', 25)->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('otp', 15)->nullable();
            $table->enum('user_type', ['1', '2'])->default(1)->comment('1->Buyer, 2->Vendor');
            $table->string('company_name')->nullable()->comment("legal name for buyer/vendor");
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_users');
    }
};
