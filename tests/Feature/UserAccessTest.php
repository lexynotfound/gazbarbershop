<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\CapsterSchedule;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function accessService(array $attributes = []): Service
{
    return Service::query()->create($attributes + [
        'name' => 'Cukur Rambut',
        'description' => 'Potongan presisi.',
        'price' => 40000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);
}

function accessCapster(array $attributes = []): Capster
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

function accessBooking(User $user, Service $service, Capster $capster, array $attributes = []): Booking
{
    $booking = Booking::query()->create($attributes + [
        'booking_code' => 'GAZ-'.fake()->unique()->bothify('??????'),
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

function accessBookingPayload(Service $service, Capster $capster): array
{
    return [
        'service_ids' => [$service->id],
        'capster_id' => $capster->id,
        'booking_date' => now()->addDay()->toDateString(),
        'booking_time' => '10:00',
    ];
}

test('admin is redirected away from user-only pages', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    $this->actingAs($admin)->get(route('booking.create'))->assertRedirect(route('admin.dashboard'));
    $this->actingAs($admin)->get(route('bookings.index'))->assertRedirect(route('admin.dashboard'));
    $this->actingAs($admin)->get(route('bookings.history'))->assertRedirect(route('admin.dashboard'));
    $this->actingAs($admin)->get(route('profile'))->assertRedirect(route('admin.dashboard'));
});

test('guest is redirected from authenticated user pages', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('bookings.index'))->assertRedirect(route('login'));
    $this->get(route('bookings.history'))->assertRedirect(route('login'));
});

test('regular user can open booking review page', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get(route('booking.review'))
        ->assertSuccessful()
        ->assertSee('Pilih Booking untuk Direview');
});

test('user dashboard statistics are calculated from current user data', function () {
    $user = User::factory()->create(['role' => 'user']);
    $otherUser = User::factory()->create(['role' => 'user']);
    $service = accessService();
    $capster = accessCapster();

    accessBooking($user, $service, $capster, ['status' => 'PENDING']);
    accessBooking($user, $service, $capster, ['status' => 'CHECKED_IN']);
    $completedBooking = accessBooking($user, $service, $capster, ['status' => 'COMPLETED']);
    $reviewedBooking = accessBooking($user, $service, $capster, ['status' => 'REVIEWED']);
    accessBooking($user, $service, $capster, ['status' => 'REJECTED']);
    accessBooking($otherUser, $service, $capster, ['status' => 'PENDING']);

    Review::query()->create([
        'booking_id' => $reviewedBooking->id,
        'user_id' => $user->id,
        'capster_id' => $capster->id,
        'rating' => 5,
        'comment' => 'Mantap.',
    ]);
    Review::query()->create([
        'booking_id' => accessBooking($otherUser, $service, $capster, ['status' => 'REVIEWED'])->id,
        'user_id' => $otherUser->id,
        'capster_id' => $capster->id,
        'rating' => 4,
        'comment' => 'Review user lain.',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Booking Aktif')
        ->assertSee('>2<', false)
        ->assertSee('Selesai')
        ->assertSee('Review')
        ->assertSee('>1<', false);

    expect($completedBooking->status)->toBe('COMPLETED');
});

test('regular user cannot access admin dashboard', function () {
    $this->actingAs(User::factory()->create(['role' => 'user']))
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('navbar points authenticated admin to admin routes', function () {
    $admin = User::factory()->create(['name' => 'Admin GAZ', 'role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('aria-label="Notifikasi"', false)
        ->assertSee('Halo, Admin')
        ->assertSee('Admin GAZ')
        ->assertSee('A')
        ->assertSee('Dashboard Admin')
        ->assertSee(route('admin.dashboard'), false)
        ->assertSee(route('admin.bookings.index'), false)
        ->assertDontSee(route('dashboard'), false)
        ->assertDontSee(route('bookings.index'), false);
});

test('navbar shows authenticated user profile menu to user dashboard', function () {
    $user = User::factory()->create(['name' => 'Member Demo', 'role' => 'user']);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('aria-label="Notifikasi"', false)
        ->assertSee('Halo, Member')
        ->assertSee('Member Demo')
        ->assertSee('M')
        ->assertSee('Dashboard User')
        ->assertSee(route('dashboard'), false)
        ->assertSee(route('booking.create'), false)
        ->assertSee(route('bookings.index'), false)
        ->assertDontSee(route('admin.dashboard'), false);
});

test('navbar keeps guest actions without profile menu', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('login'), false)
        ->assertSee(route('booking.create'), false)
        ->assertDontSee('aria-label="Notifikasi"', false)
        ->assertDontSee('Halo,')
        ->assertDontSee('Dashboard User')
        ->assertDontSee('Dashboard Admin');
});

test('booking saya only shows bookings owned by the current user', function () {
    $user = User::factory()->create(['name' => 'Member Demo', 'role' => 'user']);
    $otherUser = User::factory()->create(['name' => 'Member Lain', 'role' => 'user']);
    $service = accessService(['name' => 'Cukur Rambut + Cuci']);
    $finishedService = accessService(['name' => 'Hair Spa Selesai']);
    $otherService = accessService(['name' => 'Warnai Rambut']);
    $capster = accessCapster(['name' => 'Rudi']);

    $ownBooking = accessBooking($user, $service, $capster, [
        'booking_code' => 'GAZ-260617-OWN',
        'grand_total' => 150000,
    ]);
    $finishedBooking = accessBooking($user, $finishedService, $capster, [
        'booking_code' => 'GAZ-260617-DONE',
        'status' => 'COMPLETED',
    ]);
    $otherBooking = accessBooking($otherUser, $otherService, $capster, [
        'booking_code' => 'GAZ-260617-OTHER',
    ]);

    $this->actingAs($user)
        ->get(route('bookings.index'))
        ->assertSuccessful()
        ->assertSee('Booking Saya')
        ->assertSee(route('bookings.history'), false)
        ->assertSee($ownBooking->booking_code)
        ->assertSee('Cukur Rambut + Cuci')
        ->assertSee('Rudi')
        ->assertSee('Rp150.000')
        ->assertDontSee($finishedBooking->booking_code)
        ->assertDontSee('Hair Spa Selesai')
        ->assertDontSee($otherBooking->booking_code)
        ->assertDontSee('Warnai Rambut');
});

test('riwayat booking only shows finished and cancelled bookings owned by current user', function () {
    $user = User::factory()->create(['name' => 'Member Demo', 'role' => 'user']);
    $otherUser = User::factory()->create(['name' => 'Member Lain', 'role' => 'user']);
    $activeService = accessService(['name' => 'Cukur Aktif']);
    $finishedService = accessService(['name' => 'Cukur Selesai']);
    $cancelledService = accessService(['name' => 'Cukur Dibatalkan']);
    $otherService = accessService(['name' => 'Cukur Orang Lain']);
    $capster = accessCapster(['name' => 'Rudi']);

    $activeBooking = accessBooking($user, $activeService, $capster, [
        'booking_code' => 'GAZ-HISTORY-ACTIVE',
        'status' => 'PENDING',
    ]);
    $finishedBooking = accessBooking($user, $finishedService, $capster, [
        'booking_code' => 'GAZ-HISTORY-DONE',
        'status' => 'COMPLETED',
    ]);
    $cancelledBooking = accessBooking($user, $cancelledService, $capster, [
        'booking_code' => 'GAZ-HISTORY-CANCELLED',
        'status' => 'REJECTED',
    ]);
    $otherBooking = accessBooking($otherUser, $otherService, $capster, [
        'booking_code' => 'GAZ-HISTORY-OTHER',
        'status' => 'COMPLETED',
    ]);

    $this->actingAs($user)
        ->get(route('bookings.history'))
        ->assertSuccessful()
        ->assertSee('Riwayat Booking')
        ->assertSee(route('bookings.index'), false)
        ->assertSee($finishedBooking->booking_code)
        ->assertSee('Cukur Selesai')
        ->assertSee($cancelledBooking->booking_code)
        ->assertSee('Cukur Dibatalkan')
        ->assertDontSee($activeBooking->booking_code)
        ->assertDontSee('Cukur Aktif')
        ->assertDontSee($otherBooking->booking_code)
        ->assertDontSee('Cukur Orang Lain');
});

test('admin cannot create a booking from booking form submission', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $service = accessService();
    $capster = accessCapster();

    $this->actingAs($admin)
        ->post(route('booking.store'), accessBookingPayload($service, $capster))
        ->assertRedirect(route('admin.dashboard'));

    expect(Booking::query()->count())->toBe(0);
});

test('admin login does not complete pending booking session data', function () {
    $service = accessService();
    $capster = accessCapster();
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->withSession(['pending_booking' => accessBookingPayload($service, $capster)])
        ->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('pending_booking');

    $this->assertAuthenticatedAs($admin);
    expect(Booking::query()->count())->toBe(0);
});

test('reject admin bookings command only rejects active admin owned bookings', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'user']);
    $service = accessService();
    $capster = accessCapster();

    $adminBooking = accessBooking($admin, $service, $capster, ['status' => 'PENDING']);
    $adminCompletedBooking = accessBooking($admin, $service, $capster, ['status' => 'COMPLETED']);
    $userBooking = accessBooking($user, $service, $capster, ['status' => 'PENDING']);

    $this->artisan('app:reject-admin-bookings')
        ->expectsOutput('Rejected 1 admin-owned active booking(s).')
        ->assertSuccessful();

    expect($adminBooking->refresh()->status)->toBe('REJECTED')
        ->and($adminCompletedBooking->refresh()->status)->toBe('COMPLETED')
        ->and($userBooking->refresh()->status)->toBe('PENDING');
});
