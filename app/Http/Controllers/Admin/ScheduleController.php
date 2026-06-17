<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Capster;
use App\Models\CapsterSchedule;
use App\Services\BookingAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $capsters = Capster::query()
            ->with(['schedules' => fn ($q) => $q->orderBy('work_date')])
            ->withCount('schedules')
            ->orderBy('name')
            ->get();

        return view('admin.schedules.index', compact('capsters'));
    }

    public function byCapster(Capster $capster): View
    {
        $schedules = $capster->schedules()
            ->orderBy('work_date')
            ->orderBy('start_time')
            ->get();

        return view('admin.schedules.by-capster', compact('capster', 'schedules'));
    }

    public function create(): View
    {
        $capsters = Capster::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.schedules.create', compact('capsters'));
    }

    public function editFirst(): RedirectResponse
    {
        $schedule = CapsterSchedule::query()
            ->orderBy('work_date')
            ->orderBy('start_time')
            ->first();

        if (! $schedule) {
            return redirect()->route('admin.schedules.create');
        }

        return redirect()->route('admin.schedules.edit', $schedule);
    }

    public function show(CapsterSchedule $schedule, BookingAvailability $availability): View
    {
        $schedule->load('capster');

        return view('admin.schedules.show', [
            'schedule' => $schedule,
            'slots' => $availability->slotsForSchedule($schedule),
            'bookings' => $availability->blockingBookingsForSchedule($schedule),
        ]);
    }

    public function edit(CapsterSchedule $schedule): View
    {
        $schedule->load('capster');

        $capsters = Capster::query()
            ->orderBy('name')
            ->get();

        return view('admin.schedules.edit', compact('schedule', 'capsters'));
    }
}
