<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\CapsterSchedule;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function bookingPayload(Service $service, Capster $capster): array
{
    return [
        'service_ids' => [$service->id],
        'capster_id' => $capster->id,
        'booking_date' => now()->addDay()->toDateString(),
        'booking_time' => '10:00',
    ];
}

function activeService(array $attributes = []): Service
{
    return Service::query()->create($attributes + [
        'name' => 'Cukur Rambut',
        'description' => 'Potongan presisi.',
        'price' => 40000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);
}

function activeCapster(array $attributes = []): Capster
{
    $capster = Capster::query()->create($attributes + [
        'name' => 'Rudi',
        'rating' => 4.9,
        'service_fee' => 50000,
        'is_active' => true,
        'description' => 'Specialist fade.',
    ]);

    CapsterSchedule::query()->create([
        'capster_id' => $capster->id,
        'work_date' => now()->addDay()->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '22:00:00',
        'is_available' => true,
    ]);

    return $capster;
}

function pendingBooking(User $user, Service $service, Capster $capster, array $attributes = []): Booking
{
    $booking = Booking::query()->create($attributes + [
        'booking_code' => 'GAZ-'.fake()->unique()->numerify('######'),
        'user_id' => $user->id,
        'capster_id' => $capster->id,
        'booking_start' => now()->addDay()->setTime(10, 0),
        'booking_end' => now()->addDay()->setTime(10, 30),
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

    return $booking;
}

test('auth pages include password visibility toggle buttons', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('x-bind:type="visible ? \'text\' : \'password\'"', false)
        ->assertSee('Tampilkan password', false);

    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSee('x-bind:type="visible ? \'text\' : \'password\'"', false)
        ->assertSee('Tampilkan password', false);
});

test('guest booking is held until registration completes', function () {
    $service = activeService();
    $capster = activeCapster();

    $this->post(route('booking.store'), bookingPayload($service, $capster))
        ->assertRedirect(route('register'))
        ->assertSessionHas('pending_booking');

    expect(Booking::query()->count())->toBe(0);

    $this->post(route('register.store'), [
        'name' => 'Member Baru',
        'email' => 'member@example.com',
        'phone' => '08123456789',
        'password' => 'password',
    ])
        ->assertRedirect(route('bookings.index'))
        ->assertSessionMissing('pending_booking');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('bookings', [
        'service_total' => 40000,
        'capster_fee' => 50000,
        'grand_total' => 90000,
        'status' => 'PENDING',
    ]);
    $this->assertDatabaseHas('booking_items', [
        'service_id' => $service->id,
        'price' => 40000,
        'duration_minutes' => 30,
    ]);
    $this->assertDatabaseHas('users', [
        'email' => 'member@example.com',
        'phone' => '628123456789',
    ]);
    $this->assertDatabaseHas('payments', [
        'amount' => 90000,
        'method' => 'cash',
        'status' => 'unpaid',
        'paid_at' => null,
    ]);
});

test('authenticated user booking is created immediately from database prices', function () {
    $service = activeService(['price' => 60000, 'duration_minutes' => 45]);
    $capster = activeCapster(['service_fee' => 45000]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('booking.store'), bookingPayload($service, $capster))
        ->assertRedirect(route('bookings.index'))
        ->assertSessionMissing('pending_booking');

    $this->assertDatabaseHas('bookings', [
        'user_id' => $user->id,
        'capster_id' => $capster->id,
        'service_total' => 60000,
        'capster_fee' => 45000,
        'grand_total' => 105000,
    ]);
    $this->assertDatabaseHas('payments', [
        'amount' => 105000,
        'method' => 'cash',
        'status' => 'unpaid',
        'paid_at' => null,
    ]);
});

test('login completes a pending booking', function () {
    $service = activeService();
    $capster = activeCapster();
    $user = User::factory()->create([
        'email' => 'member@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->withSession(['pending_booking' => bookingPayload($service, $capster)])
        ->post(route('login.store'), [
            'email' => 'member@example.com',
            'password' => 'password',
        ])
        ->assertRedirect(route('bookings.index'))
        ->assertSessionMissing('pending_booking');

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('bookings', [
        'user_id' => $user->id,
        'grand_total' => 90000,
    ]);
});

test('admin dashboard shows pending booking action routes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['name' => 'Rizky Pratama']);
    $service = activeService(['name' => 'Cukur Favorit']);
    $capster = activeCapster(['name' => 'Rudi']);
    $booking = pendingBooking($customer, $service, $capster);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee('Booking Menunggu Konfirmasi')
        ->assertSee('Rizky Pratama')
        ->assertSee('Cukur Favorit')
        ->assertSee(route('admin.bookings.whatsapp', $booking), false)
        ->assertSee(route('admin.bookings.confirm', $booking), false)
        ->assertSee('Tandai WA Terkirim')
        ->assertSee('WhatsApp');
});

test('admin dashboard booking chart uses booking totals from the last thirty days', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create();
    $service = activeService();
    $capster = activeCapster();

    pendingBooking($customer, $service, $capster, [
        'booking_start' => today()->setTime(9, 0),
        'booking_end' => today()->setTime(9, 30),
    ]);
    pendingBooking($customer, $service, $capster, [
        'booking_start' => today()->setTime(10, 0),
        'booking_end' => today()->setTime(10, 30),
    ]);
    pendingBooking($customer, $service, $capster, [
        'booking_start' => today()->subDay()->setTime(11, 0),
        'booking_end' => today()->subDay()->setTime(11, 30),
    ]);
    pendingBooking($customer, $service, $capster, [
        'booking_start' => today()->subDays(30)->setTime(12, 0),
        'booking_end' => today()->subDays(30)->setTime(12, 30),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee(route('admin.booking-chart'), false)
        ->assertSee('Lihat Detail')
        ->assertSee(today()->translatedFormat('d M').': 2 booking')
        ->assertSee(today()->subDay()->translatedFormat('d M').': 1 booking')
        ->assertDontSee(today()->subDays(30)->translatedFormat('d M').': 1 booking');
});

test('admin booking chart page shows chart and detail table from the last thirty days', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create();
    $service = activeService();
    $capster = activeCapster();

    pendingBooking($customer, $service, $capster, [
        'booking_start' => today()->setTime(9, 0),
        'booking_end' => today()->setTime(9, 30),
    ]);
    pendingBooking($customer, $service, $capster, [
        'booking_start' => today()->setTime(10, 0),
        'booking_end' => today()->setTime(10, 30),
    ]);
    pendingBooking($customer, $service, $capster, [
        'booking_start' => today()->subDay()->setTime(11, 0),
        'booking_end' => today()->subDay()->setTime(11, 30),
    ]);
    pendingBooking($customer, $service, $capster, [
        'booking_start' => today()->subDays(30)->setTime(12, 0),
        'booking_end' => today()->subDays(30)->setTime(12, 30),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.booking-chart'))
        ->assertSuccessful()
        ->assertSee('Booking Terbaru')
        ->assertSee('Detail Data')
        ->assertSee(route('admin.dashboard'), false)
        ->assertSee(today()->translatedFormat('d M').': 2 booking')
        ->assertSee(today()->subDay()->translatedFormat('d M').': 1 booking')
        ->assertDontSee(today()->subDays(30)->translatedFormat('d M').': 1 booking');
});

test('admin bookings page lists bookings with valid whatsapp links', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['name' => 'Ariel Saputra']);
    $service = activeService(['name' => 'Cukur Trendcut']);
    $capster = activeCapster(['name' => 'Dika']);
    $booking = pendingBooking($customer, $service, $capster);

    $this->actingAs($admin)
        ->get(route('admin.bookings.index'))
        ->assertSuccessful()
        ->assertSee('List Booking')
        ->assertSee('1 Booking')
        ->assertSee($booking->booking_code)
        ->assertSee('Ariel Saputra')
        ->assertSee('Cukur Trendcut')
        ->assertSee(route('admin.bookings.show', $booking), false)
        ->assertSee(route('admin.bookings.whatsapp', $booking), false);
});

test('admin booking detail and whatsapp pages include breadcrumbs back to list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create([
        'name' => 'Dedi Santoso',
        'phone' => '08123456789',
    ]);
    $service = activeService(['name' => 'Warnai Rambut']);
    $capster = activeCapster(['name' => 'Bayu']);
    $booking = pendingBooking($customer, $service, $capster);

    $this->actingAs($admin)
        ->get(route('admin.bookings.show', $booking))
        ->assertSuccessful()
        ->assertSee('Booking')
        ->assertSee($booking->booking_code)
        ->assertSee(route('admin.bookings.index'), false)
        ->assertSee(route('admin.bookings.whatsapp', $booking), false);

    $this->actingAs($admin)
        ->get(route('admin.bookings.whatsapp', $booking))
        ->assertSuccessful()
        ->assertSee('WhatsApp')
        ->assertSee('Tolak Booking')
        ->assertSee($booking->booking_code)
        ->assertSee('https://wa.me/628123456789?text=', false)
        ->assertSee(route('admin.bookings.index'), false)
        ->assertSee(route('admin.bookings.show', $booking), false);
});

test('admin can mark whatsapp as sent for a pending booking', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create();
    $service = activeService();
    $capster = activeCapster();
    $booking = pendingBooking($customer, $service, $capster);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.confirm', $booking))
        ->assertRedirect(route('admin.bookings.index'));

    $booking->refresh();

    expect($booking->status)->toBe('WAITING_CUSTOMER_CONFIRMATION')
        ->and($booking->admin_confirmed_at)->not->toBeNull()
        ->and($booking->customer_response_deadline)->not->toBeNull();
});

test('admin can mark a whatsapp-confirmed user as accepted', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create();
    $service = activeService();
    $capster = activeCapster();
    $booking = pendingBooking($customer, $service, $capster, [
        'status' => 'WAITING_CUSTOMER_CONFIRMATION',
        'admin_confirmed_at' => now(),
        'customer_response_deadline' => now()->addMinutes(10),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.bookings.show', $booking))
        ->assertSuccessful()
        ->assertSee('Menunggu Balasan WA')
        ->assertDontSee('Waiting Customer Confirmation')
        ->assertSee(route('admin.bookings.accept', $booking), false)
        ->assertSee('User Jadi Datang');

    $this->actingAs($admin)
        ->patch(route('admin.bookings.accept', $booking))
        ->assertRedirect(route('admin.bookings.show', $booking));

    $booking->refresh();

    expect($booking->status)->toBe('CONFIRMED')
        ->and($booking->customer_response_deadline)->toBeNull();
});

test('admin can check in an active booking', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create();
    $service = activeService();
    $capster = activeCapster();
    $booking = pendingBooking($customer, $service, $capster, [
        'status' => 'CONFIRMED',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.bookings.show', $booking))
        ->assertSuccessful()
        ->assertSee(route('admin.bookings.check-in', $booking), false)
        ->assertSee('Check-in');

    $this->actingAs($admin)
        ->patch(route('admin.bookings.check-in', $booking))
        ->assertRedirect(route('admin.bookings.show', $booking));

    $booking->refresh();

    expect($booking->status)->toBe('CHECKED_IN')
        ->and($booking->checked_in_at)->not->toBeNull();
});

test('admin can complete a checked in booking so the user can review it', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user']);
    $service = activeService(['name' => 'Cukur Rambut + Cuci']);
    $capster = activeCapster(['name' => 'Rudi']);
    $booking = pendingBooking($customer, $service, $capster, [
        'booking_code' => 'GAZ-READY-REVIEW',
        'status' => 'CHECKED_IN',
        'checked_in_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.bookings.show', $booking))
        ->assertSuccessful()
        ->assertSee(route('admin.bookings.complete', $booking), false)
        ->assertSee('Selesaikan Booking');

    $this->actingAs($admin)
        ->patch(route('admin.bookings.complete', $booking))
        ->assertRedirect(route('admin.bookings.show', $booking));

    $booking->refresh();

    expect($booking->status)->toBe('COMPLETED')
        ->and($booking->completed_at)->not->toBeNull();

    $this->actingAs($customer)
        ->get(route('booking.review'))
        ->assertSuccessful()
        ->assertSee('Pilih Booking untuk Direview')
        ->assertSee('GAZ-READY-REVIEW')
        ->assertSee('Cukur Rambut + Cuci')
        ->assertSee('Rudi')
        ->assertSee(route('booking.review', ['booking' => $booking->id]), false);
});

test('admin can mark a booking payment as paid with a selected method', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user']);
    $service = activeService(['name' => 'Cukur Rambut']);
    $capster = activeCapster(['name' => 'Rudi']);
    $booking = pendingBooking($customer, $service, $capster, [
        'booking_code' => 'GAZ-PAID-QRIS',
    ]);

    $payment = Payment::query()->create([
        'booking_id' => $booking->id,
        'amount' => $booking->grand_total,
        'method' => 'cash',
        'status' => 'unpaid',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.bookings.show', $booking))
        ->assertSuccessful()
        ->assertSee('Belum Dibayar')
        ->assertSee(route('admin.bookings.payment.paid', $booking), false)
        ->assertSee('Tandai Lunas');

    $this->actingAs($admin)
        ->patch(route('admin.bookings.payment.paid', $booking), [
            'method' => 'qris',
        ])
        ->assertRedirect(route('admin.bookings.show', $booking));

    $payment->refresh();

    expect($payment->status)->toBe('paid')
        ->and($payment->method)->toBe('qris')
        ->and($payment->amount)->toBe($booking->grand_total)
        ->and($payment->paid_at)->not->toBeNull();
});

test('backfill booking payments command creates missing payments for old bookings', function () {
    $customer = User::factory()->create(['role' => 'user']);
    $service = activeService();
    $capster = activeCapster();
    $completedBooking = pendingBooking($customer, $service, $capster, [
        'booking_code' => 'GAZ-260621-BZZSAI',
        'status' => 'COMPLETED',
        'completed_at' => now()->subHour(),
        'grand_total' => 360000,
    ]);
    $pendingBooking = pendingBooking($customer, $service, $capster, [
        'booking_code' => 'GAZ-PENDING-BACKFILL',
        'status' => 'PENDING',
        'grand_total' => 150000,
    ]);

    $this->artisan('app:backfill-booking-payments')
        ->expectsOutput('Created 2 missing payment(s).')
        ->expectsOutput('Marked 1 payment(s) as paid.')
        ->expectsOutput('Marked 1 payment(s) as unpaid.')
        ->assertSuccessful();

    $this->assertDatabaseHas('payments', [
        'booking_id' => $completedBooking->id,
        'amount' => 360000,
        'method' => 'cash',
        'status' => 'paid',
    ]);
    $this->assertDatabaseHas('payments', [
        'booking_id' => $pendingBooking->id,
        'amount' => 150000,
        'method' => 'cash',
        'status' => 'unpaid',
        'paid_at' => null,
    ]);
});

test('admin can reject a booking from whatsapp confirmation action', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create();
    $service = activeService();
    $capster = activeCapster();
    $booking = pendingBooking($customer, $service, $capster);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.cancel', $booking))
        ->assertRedirect(route('admin.bookings.index'));

    expect($booking->refresh()->status)->toBe('CANCELLED');
});

test('late booking command cancels unconfirmed and late accepted bookings', function () {
    $customer = User::factory()->create();
    $service = activeService();
    $capster = activeCapster();
    $lateBooking = pendingBooking($customer, $service, $capster, [
        'booking_start' => now()->subMinutes(16),
        'booking_end' => now()->addMinutes(14),
        'status' => 'CONFIRMED',
    ]);
    $pendingLateBooking = pendingBooking($customer, $service, $capster, [
        'booking_start' => now()->subMinutes(17),
        'booking_end' => now()->addMinutes(13),
        'status' => 'PENDING',
    ]);
    $unconfirmedBooking = pendingBooking($customer, $service, $capster, [
        'booking_start' => now()->addHour(),
        'booking_end' => now()->addMinutes(90),
        'status' => 'WAITING_CUSTOMER_CONFIRMATION',
        'customer_response_deadline' => now()->subMinute(),
    ]);
    $recentBooking = pendingBooking($customer, $service, $capster, [
        'booking_start' => now()->subMinutes(14),
        'booking_end' => now()->addMinutes(16),
        'status' => 'CONFIRMED',
    ]);
    $checkedInBooking = pendingBooking($customer, $service, $capster, [
        'booking_start' => now()->subMinutes(20),
        'booking_end' => now()->addMinutes(10),
        'status' => 'CHECKED_IN',
        'checked_in_at' => now()->subMinutes(18),
    ]);
    $completedBooking = pendingBooking($customer, $service, $capster, [
        'booking_start' => now()->subMinutes(20),
        'booking_end' => now()->addMinutes(10),
        'status' => 'COMPLETED',
    ]);

    $this->artisan('app:cancel-late-bookings')
        ->expectsOutput('Auto-cancelled 1 unconfirmed booking(s).')
        ->expectsOutput('Late-cancelled 2 booking(s).')
        ->assertSuccessful();

    expect($lateBooking->refresh()->status)->toBe('LATE_CANCELLED')
        ->and($pendingLateBooking->refresh()->status)->toBe('LATE_CANCELLED')
        ->and($unconfirmedBooking->refresh()->status)->toBe('AUTO_CANCELLED')
        ->and($recentBooking->refresh()->status)->toBe('CONFIRMED')
        ->and($checkedInBooking->refresh()->status)->toBe('CHECKED_IN')
        ->and($completedBooking->refresh()->status)->toBe('COMPLETED');
});

test('booking availability endpoint marks active bookings as unavailable', function () {
    $service = activeService(['duration_minutes' => 60]);
    $capster = activeCapster();
    $customer = User::factory()->create();
    pendingBooking($customer, $service, $capster, [
        'booking_start' => now()->addDay()->setTime(10, 0),
        'booking_end' => now()->addDay()->setTime(11, 0),
    ]);

    $response = $this->getJson(route('booking.available-times', [
        'capster_id' => $capster->id,
        'booking_date' => now()->addDay()->toDateString(),
        'duration_minutes' => 60,
    ]));

    $response->assertSuccessful();

    $slot = collect($response->json('slots'))->firstWhere('time', '10:00');

    expect($slot['available'])->toBeFalse()
        ->and($slot['status'])->toBe('Terbooking');
});

test('user cannot book an occupied active slot', function () {
    $service = activeService(['duration_minutes' => 60]);
    $capster = activeCapster();
    $customer = User::factory()->create();
    pendingBooking($customer, $service, $capster, [
        'booking_start' => now()->addDay()->setTime(10, 0),
        'booking_end' => now()->addDay()->setTime(11, 0),
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('booking.store'), bookingPayload($service, $capster))
        ->assertSessionHasErrors('booking_time');

    expect(Booking::query()->count())->toBe(1);
});

test('completed booking does not block the same slot', function () {
    $service = activeService(['duration_minutes' => 60]);
    $capster = activeCapster();
    $customer = User::factory()->create();
    pendingBooking($customer, $service, $capster, [
        'booking_start' => now()->addDay()->setTime(10, 0),
        'booking_end' => now()->addDay()->setTime(11, 0),
        'status' => 'COMPLETED',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('booking.store'), bookingPayload($service, $capster))
        ->assertRedirect(route('bookings.index'));

    expect(Booking::query()->count())->toBe(2);
});

test('booking duration blocks every overlapping slot', function () {
    $longService = activeService(['duration_minutes' => 90]);
    $shortService = activeService(['name' => 'Rapikan Rambut', 'duration_minutes' => 30]);
    $capster = activeCapster();
    $customer = User::factory()->create();
    pendingBooking($customer, $longService, $capster, [
        'booking_start' => now()->addDay()->setTime(10, 0),
        'booking_end' => now()->addDay()->setTime(11, 30),
    ]);

    $response = $this->getJson(route('booking.available-times', [
        'capster_id' => $capster->id,
        'booking_date' => now()->addDay()->toDateString(),
        'duration_minutes' => $shortService->duration_minutes,
    ]));

    $response->assertSuccessful();

    expect(collect($response->json('slots'))->firstWhere('time', '11:00')['available'])->toBeFalse();

    $this->actingAs(User::factory()->create())
        ->post(route('booking.store'), [
            ...bookingPayload($shortService, $capster),
            'booking_time' => '11:00',
        ])
        ->assertSessionHasErrors('booking_time');

    expect(Booking::query()->count())->toBe(1);
});
