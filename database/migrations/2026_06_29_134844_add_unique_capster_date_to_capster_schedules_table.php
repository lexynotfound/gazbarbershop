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
        Schema::table('capster_schedules', function (Blueprint $table) {
            $table->unique(['capster_id', 'work_date'], 'capster_schedules_capster_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capster_schedules', function (Blueprint $table) {
            $table->dropUnique('capster_schedules_capster_date_unique');
        });
    }
};
