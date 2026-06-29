<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('bookings')->where('status', 'ACCEPTED')->update(['status' => 'CONFIRMED']);
        DB::table('bookings')->where('status', 'REJECTED')->update(['status' => 'CANCELLED']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('bookings')->where('status', 'CONFIRMED')->update(['status' => 'ACCEPTED']);
        DB::table('bookings')->where('status', 'CANCELLED')->update(['status' => 'REJECTED']);
    }
};
