<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\CapsterSchedule;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function scheduleCapster(string $name): Capster
{
    return Capster::query()->create([
        'name' => $name,
        'rating' => 4.8,
        'service_fee' => 50000,
        'is_active' => true,
        'description' => 'Capster jadwal.',
    ]);
}

function capsterSchedule(Capster $capster, array $attributes = []): CapsterSchedule
{
    return CapsterSchedule::query()->create($attributes + [
        'capster_id' => $capster->id,
        'work_date' => '2026-05-31',
        'start_time' => '08:00:00',
        'end_time' => '18:00:00',
        'is_available' => true,
    ]);
}

test('admin schedules index shows one card per capster with link to their schedule', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $rudi = scheduleCapster('Rudi');
    $dika = scheduleCapster('Dika');
    $bayu = scheduleCapster('Bayu');
    capsterSchedule($rudi, ['work_date' => '2026-05-31', 'start_time' => '08:00:00', 'end_time' => '18:00:00']);
    capsterSchedule($dika, ['work_date' => '2026-05-31', 'start_time' => '10:00:00', 'end_time' => '20:00:00']);
    capsterSchedule($bayu, ['work_date' => '2026-06-01', 'start_time' => '12:00:00', 'end_time' => '20:00:00', 'is_available' => false]);

    $this->actingAs($admin)
        ->get(route('admin.schedules.index'))
        ->assertSuccessful()
        ->assertSee('List Jadwal Capster')
        ->assertSee('Rudi')
        ->assertSee('Dika')
        ->assertSee('Bayu')
        ->assertSee(route('admin.schedules.by-capster', $rudi), false)
        ->assertSee(route('admin.schedules.by-capster', $dika), false)
        ->assertSee(route('admin.schedules.by-capster', $bayu), false);
});

test('capster schedule page lists that capsters clickable schedule cards', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $rudi = scheduleCapster('Rudi');
    $dika = scheduleCapster('Dika');
    $rudiSchedule = capsterSchedule($rudi, ['work_date' => '2026-05-31', 'start_time' => '08:00:00', 'end_time' => '18:00:00']);
    $dikaSchedule = capsterSchedule($dika, ['work_date' => '2026-05-31', 'start_time' => '10:00:00', 'end_time' => '20:00:00']);

    $this->actingAs($admin)
        ->get(route('admin.schedules.by-capster', $rudi))
        ->assertSuccessful()
        ->assertSee('Rudi')
        ->assertSee('31 May 2026')
        ->assertSee('08:00 - 18:00')
        ->assertDontSee('Dika')
        ->assertSee(route('admin.schedules.show', $rudiSchedule), false)
        ->assertDontSee(route('admin.schedules.show', $dikaSchedule), false);
});

test('admin schedule card opens detail page with availability slots', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['name' => 'Rizky Pratama']);
    $capster = scheduleCapster('Rudi');
    $service = Service::query()->create([
        'name' => 'Cukur Rambut',
        'description' => 'Potongan presisi.',
        'price' => 40000,
        'duration_minutes' => 60,
        'is_active' => true,
    ]);
    $schedule = capsterSchedule($capster, [
        'work_date' => '2026-05-31',
        'start_time' => '08:00:00',
        'end_time' => '18:00:00',
    ]);
    $booking = Booking::query()->create([
        'booking_code' => 'GAZ-TEST-001',
        'user_id' => $customer->id,
        'capster_id' => $capster->id,
        'booking_start' => '2026-05-31 10:00:00',
        'booking_end' => '2026-05-31 11:00:00',
        'service_total' => $service->price,
        'capster_fee' => $capster->service_fee,
        'grand_total' => $service->price + $capster->service_fee,
        'status' => 'PENDING',
    ]);
    $booking->items()->create([
        'service_id' => $service->id,
        'price' => $service->price,
        'duration_minutes' => $service->duration_minutes,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.schedules.show', $schedule))
        ->assertSuccessful()
        ->assertSee('Detail Jadwal Capster')
        ->assertSee('08:00-09:00')
        ->assertSee('10:00-11:00')
        ->assertSee('Terbooking')
        ->assertSee('GAZ-TEST-001')
        ->assertSee('Rizky Pratama')
        ->assertSee(route('admin.schedules.edit', $schedule), false);
});

test('legacy schedule edit url redirects to first schedule edit page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $capster = scheduleCapster('Rudi');
    $schedule = capsterSchedule($capster);

    $this->actingAs($admin)
        ->get('/admin/schedules/edit')
        ->assertRedirect(route('admin.schedules.edit', $schedule));
});
