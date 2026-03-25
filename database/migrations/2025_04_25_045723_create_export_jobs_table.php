<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('export_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('export_id', 36)->unique();
            $table->string('type'); // e.g., 'verified_products'
            $table->string('file_name');
            $table->string('language')->default('English');
            $table->integer('record_count')->nullable();
            $table->string('status')->default('processing'); // processing, completed, failed
            $table->text('error_message')->nullable();
            $table->string('disk')->default('local');
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->timestamp('completed_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('export_jobs');
    }
};