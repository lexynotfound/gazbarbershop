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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('capster_id')->constrained()->restrictOnDelete();
            $table->dateTime('booking_start')->index();
            $table->dateTime('booking_end')->index();
            $table->unsignedInteger('service_total');
            $table->unsignedInteger('capster_fee');
            $table->unsignedInteger('grand_total');
            $table->string('status')->default('PENDING')->index();
            $table->dateTime('admin_confirmed_at')->nullable();
            $table->dateTime('customer_response_deadline')->nullable();
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['capster_id', 'booking_start', 'booking_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
