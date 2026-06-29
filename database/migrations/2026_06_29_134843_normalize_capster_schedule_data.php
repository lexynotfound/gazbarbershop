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
        DB::table('capster_schedules')
            ->orderBy('capster_id')
            ->orderBy('work_date')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (object $schedule): string => $schedule->capster_id.'|'.$schedule->work_date)
            ->each(function ($schedules): void {
                $schedule = $schedules->first();
                $duplicateIds = $schedules->skip(1)->pluck('id');

                if ($duplicateIds->isNotEmpty()) {
                    DB::table('capster_schedules')->whereIn('id', $duplicateIds)->delete();
                }

                $startTime = substr((string) $schedule->start_time, 0, 5);
                $endTime = substr((string) $schedule->end_time, 0, 5);
                $isInvalid = $startTime < '10:00'
                    || $startTime >= '22:00'
                    || $endTime <= '10:00'
                    || $endTime > '22:00'
                    || $endTime <= $startTime;

                if ($isInvalid) {
                    DB::table('capster_schedules')
                        ->where('id', $schedule->id)
                        ->update([
                            'start_time' => '10:00:00',
                            'end_time' => '22:00:00',
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data normalization is intentionally irreversible.
    }
};
