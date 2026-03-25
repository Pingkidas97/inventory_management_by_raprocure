<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('issued_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branch_details')->onDelete('cascade');
            $table->bigInteger('issue_unique_no')->unique();
            $table->string('qty', 20)->nullable();
            $table->string('vendor_name', 255)->nullable();
            $table->bigInteger('issued_return_for')->default(0)->comment('grn-id');
            $table->string('remarks', 255)->nullable();
            $table->integer('issued_return_type')->default(0);
            $table->integer('is_deleted')->default(2)->comment('2-not delete, 1-delete');
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');

            // Use Laravel's default timestamps
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('issued_returns');
    }
};

