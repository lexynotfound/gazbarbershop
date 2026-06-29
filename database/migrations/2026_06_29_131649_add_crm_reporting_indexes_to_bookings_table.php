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
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['status', 'booking_start'], 'bookings_crm_status_start_index');
            $table->index(['user_id', 'status', 'booking_start'], 'bookings_crm_user_status_start_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_crm_status_start_index');
            $table->dropIndex('bookings_crm_user_status_start_index');
        });
    }
};
