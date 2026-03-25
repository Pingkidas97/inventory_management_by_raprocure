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
        Schema::create('help_supports', function (Blueprint $table) {
            $table->id();
            $table->string('request_id', 200)->nullable();
            $table->string('document', 255)->nullable();
            $table->string('issue_type', 255)->nullable();
            $table->string('description',10000)->nullable(); 
            $table->enum('user_type', ['1', '2', '3'])->default('1')->comment('1->Buyer, 2->Vendor, 3->Super Admin');
            $table->bigInteger('company_id')->nullable();
            $table->enum('status', ['1', '2', '3'])->nullable()->comment('1 => Pending, 2 => In-process, 3 => Finally closed');
            $table->timestamp('created_at')->useCurrent();
            $table->bigInteger('created_by');
            $table->timestamp('updated_at')
                  ->useCurrent()
                  ->useCurrentOnUpdate();
            $table->bigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_supports');
    }
};
