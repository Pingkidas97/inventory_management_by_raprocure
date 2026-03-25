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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('email', 60)->nullable();
            $table->integer('country_code')->nullable();
            $table->string('mobile', 25)->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->enum('status', ['1', '2'])->comment('1->Active, 2->Inactive');
            $table->enum('is_verified', ['1', '2'])->default('2')->comment('1-> Verified, 2-> Unverified');
            $table->enum('is_profile_verified', ['1', '2'])->default('2')->comment('1-> New Profile verified, 2-> New Profile not verified');
            $table->bigInteger('verified_by')->nullable();
            $table->enum('user_type', ['1', '2', '3'])->default('1')->comment('1->Buyer, 2->Vendor, 3->Super Admin');
            $table->string('designation')->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->integer('reset_password_attemts')->default(0);
            $table->date('reset_password_date')->nullable();
            $table->bigInteger('user_created_by')->nullable();
            $table->bigInteger('user_updated_by')->nullable();
            $table->tinyInteger('currency')->default(0);
            $table->tinyInteger('is_change_currency')->default(1);
            $table->boolean('is_api_enable')->default(0)->comment('1->API Enable');
            $table->boolean('is_inventory_enable')->default(1)->comment('1->enable');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token', 191);
            $table->timestamp('created_at')->nullable();
        });
        

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
