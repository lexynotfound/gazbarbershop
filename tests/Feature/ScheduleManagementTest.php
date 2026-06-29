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
        'start_time' => '10:00:00',
        'end_time' => '22:00:00',
        'is_available' => true,
    ]);
}

function schedulePayload(Capster $capster, array $attributes = []): array
{
    return $attributes + [
        'capster_id' => $capster->id,
        'work_date' => '2026-07-01',
        'start_time' => '10:00',
        'end_time' => '22:00',
        'is_available' => '1',
    ];
}

test('admin schedules index shows one card per capster with link to their schedule', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $rudi = scheduleCapster('Rudi');
    $dika = scheduleCapster('Dika');
    $bayu = scheduleCapster('Bayu');
    capsterSchedule($rudi, ['work_date' => '2026-05-31', 'start_time' => '10:00:00', 'end_time' => '22:00:00']);
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
    $rudiSchedule = capsterSchedule($rudi, ['work_date' => '2026-05-31', 'start_time' => '10:00:00', 'end_time' => '22:00:00']);
    $dikaSchedule = capsterSchedule($dika, ['work_date' => '2026-05-31', 'start_time' => '10:00:00', 'end_time' => '20:00:00']);

    $this->actingAs($admin)
        ->get(route('admin.schedules.by-capster', $rudi))
        ->assertSuccessful()
        ->assertSee('Rudi')
        ->assertSee('31 May 2026')
        ->assertSee('10:00 - 22:00')
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
        'start_time' => '10:00:00',
        'end_time' => '22:00:00',
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
        ->assertSee('10:00-11:00')
        ->assertSee('21:00-22:00')
        ->assertDontSee('09:00-10:00')
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

test('admin create and edit forms use persistence routes and operating hour defaults', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $capster = scheduleCapster('Rudi');
    $schedule = capsterSchedule($capster);

    $this->actingAs($admin)
        ->get(route('admin.schedules.create', ['capster' => $capster->id]))
        ->assertSuccessful()
        ->assertSee(route('admin.schedules.store'), false)
        ->assertSee('value="10:00"', false)
        ->assertSee('value="22:00"', false);

    $this->actingAs($admin)
        ->get(route('admin.schedules.edit', $schedule))
        ->assertSuccessful()
        ->assertSee(route('admin.schedules.update', $schedule), false)
        ->assertSee('value="PATCH"', false);
});

test('admin can add a capster schedule', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $capster = scheduleCapster('Rudi');

    $response = $this->actingAs($admin)
        ->post(route('admin.schedules.store'), schedulePayload($capster));

    $schedule = CapsterSchedule::query()->sole();

    $response
        ->assertRedirect(route('admin.schedules.show', $schedule))
        ->assertSessionHas('status', 'Jadwal capster berhasil ditambahkan.');

    expect($schedule->capster_id)->toBe($capster->id)
        ->and($schedule->work_date->toDateString())->toBe('2026-07-01')
        ->and($schedule->start_time)->toBe('10:00')
        ->and($schedule->end_time)->toBe('22:00')
        ->and($schedule->is_available)->toBeTrue();
});

test('admin can edit a capster schedule', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $capster = scheduleCapster('Rudi');
    $schedule = capsterSchedule($capster);

    $this->actingAs($admin)
        ->patch(route('admin.schedules.update', $schedule), schedulePayload($capster, [
            'work_date' => '2026-07-02',
            'start_time' => '11:00',
            'end_time' => '20:00',
            'is_available' => '0',
        ]))
        ->assertRedirect(route('admin.schedules.show', $schedule))
        ->assertSessionHas('status', 'Jadwal capster berhasil diperbarui.');

    $schedule->refresh();

    expect($schedule->work_date->toDateString())->toBe('2026-07-02')
        ->and($schedule->start_time)->toBe('11:00')
        ->and($schedule->end_time)->toBe('20:00')
        ->and($schedule->is_available)->toBeFalse();
});

test('schedule times must stay within operating hours and end after start', function (string $startTime, string $endTime, string $errorField) {
    $admin = User::factory()->create(['role' => 'admin']);
    $capster = scheduleCapster('Rudi');

    $this->actingAs($admin)
        ->post(route('admin.schedules.store'), schedulePayload($capster, [
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]))
        ->assertSessionHasErrors($errorField);

    expect(CapsterSchedule::query()->count())->toBe(0);
})->with([
    'before opening' => ['09:30', '18:00', 'start_time'],
    'after closing' => ['10:00', '22:30', 'end_time'],
    'end before start' => ['20:00', '19:00', 'end_time'],
]);

test('a capster can only have one schedule per date', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $capster = scheduleCapster('Rudi');
    capsterSchedule($capster, ['work_date' => '2026-07-01']);

    $this->actingAs($admin)
        ->post(route('admin.schedules.store'), schedulePayload($capster))
        ->assertSessionHasErrors('work_date');

    expect(CapsterSchedule::query()->count())->toBe(1);
});

test('regular users cannot add or edit capster schedules', function () {
    $user = User::factory()->create(['role' => 'user']);
    $capster = scheduleCapster('Rudi');
    $schedule = capsterSchedule($capster);

    $this->actingAs($user)
        ->post(route('admin.schedules.store'), schedulePayload($capster, ['work_date' => '2026-07-01']))
        ->assertForbidden();

    $this->actingAs($user)
        ->patch(route('admin.schedules.update', $schedule), schedulePayload($capster))
        ->assertForbidden();
});

test('schedule data migration normalizes invalid operating hours', function () {
    $capster = scheduleCapster('Rudi');
    $schedule = capsterSchedule($capster, [
        'start_time' => '08:00:00',
        'end_time' => '18:00:00',
    ]);

    $migration = require database_path('migrations/2026_06_29_134843_normalize_capster_schedule_data.php');
    $migration->up();

    $schedule->refresh();

    expect(str($schedule->start_time)->substr(0, 5)->toString())->toBe('10:00')
        ->and(str($schedule->end_time)->substr(0, 5)->toString())->toBe('22:00');
});
