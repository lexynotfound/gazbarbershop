<?php

use App\Models\Booking;
use App\Models\Capster;
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
    return Capster::query()->create($attributes + [
        'name' => 'Rudi',
        'rating' => 4.9,
        'service_fee' => 50000,
        'is_active' => true,
        'description' => 'Specialist fade.',
    ]);
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

