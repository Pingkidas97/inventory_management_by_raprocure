<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grns', function (Blueprint $table) {
            $table->string('bill_date')->nullable()->after('vendor_invoice_number');
            $table->string('transporter_name')->nullable()->after('bill_date');
            $table->string('vehicle_no_lr_no', 200)
                  ->charset('utf8mb4')
                  ->collation('utf8mb4_general_ci')
                  ->nullable()
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('grns', function (Blueprint $table) {
            $table->dropColumn('bill_date');
            $table->dropColumn('transporter_name');
            $table->string('vehicle_no_lr_no')->change();
        });
    }
};
