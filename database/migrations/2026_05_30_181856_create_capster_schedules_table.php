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
        Schema::create('capster_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capster_id')->constrained()->cascadeOnDelete();
            $table->date('work_date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true)->index();
            $table->timestamps();

            $table->index(['capster_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capster_schedules');
    }
};
