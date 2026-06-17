<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\CapsterSchedule;
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
        'start_time' => '08:00:00',
        'end_time' => '18:00:00',
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
        'phone' => '628123456789',
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
        ->assertSee('Konfirmasi')
        ->assertSee('WhatsApp');
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
    $customer = User::factory()->create(['name' => 'Dedi Santoso']);
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
        ->assertSee(route('admin.bookings.index'), false)
        ->assertSee(route('admin.bookings.show', $booking), false);
});

test('admin can confirm a pending booking from dashboard action', function () {
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

test('admin can reject a booking from whatsapp confirmation action', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create();
    $service = activeService();
    $capster = activeCapster();
    $booking = pendingBooking($customer, $service, $capster);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.cancel', $booking))
        ->assertRedirect(route('admin.bookings.index'));

    expect($booking->refresh()->status)->toBe('REJECTED');
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
