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
        Schema::create('mail_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('subject')->nullable();
            $table->text('mail_data')->nullable();
            $table->integer('is_send')->default(2)->comment('2->Pending, 3->being sent, 4->sent once');
            $table->dateTime('sent_date_time')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_data');
    }
};
