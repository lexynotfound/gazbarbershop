<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\CapsterSchedule;
use App\Models\Service;
use App\Models\User;
use App\Notifications\BookingRescheduledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function rescheduleService(array $attributes = []): Service
{
    return Service::query()->create($attributes + [
        'name' => 'Cukur Rambut',
        'description' => 'Potongan presisi.',
        'price' => 40000,
        'duration_minutes' => 60,
        'is_active' => true,
    ]);
}

function rescheduleCapster(array $attributes = []): Capster
{
    $capster = Capster::query()->create($attributes + [
        'name' => 'Rudi',
        'rating' => 4.9,
        'service_fee' => 50000,
        'is_active' => true,
        'description' => 'Specialist fade.',
    ]);

    foreach ([now()->addDay(), now()->addDays(2)] as $workDate) {
        CapsterSchedule::query()->create([
            'capster_id' => $capster->id,
            'work_date' => $workDate->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '22:00:00',
            'is_available' => true,
        ]);
    }

    return $capster;
}

function rescheduleBooking(User $user, Service $service, Capster $capster, array $attributes = []): Booking
{
    $booking = Booking::query()->create($attributes + [
        'booking_code' => 'GAZ-'.fake()->unique()->numerify('######'),
        'user_id' => $user->id,
        'capster_id' => $capster->id,
        'booking_start' => now()->addDay()->setTime(10, 0),
        'booking_end' => now()->addDay()->setTime(11, 0),
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

test('admin can reschedule an active booking to a new available slot', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create();
    $service = rescheduleService();
    $capster = rescheduleCapster();
    $booking = rescheduleBooking($customer, $service, $capster);

    Notification::fake();

    $this->actingAs($admin)
        ->patch(route('admin.bookings.reschedule', $booking), [
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '11:00',
        ])
        ->assertRedirect(route('admin.bookings.show', $booking));

    $booking->refresh();

    expect($booking->status)->toBe('PENDING')
        ->and($booking->booking_start->toDateString())->toBe(now()->addDays(2)->toDateString())
        ->and($booking->booking_start->format('H:i'))->toBe('11:00');

    Notification::assertSentTo(
        $customer,
        BookingRescheduledNotification::class,
        fn (BookingRescheduledNotification $notification): bool => $notification->booking->is($booking)
            && $notification->previousBookingStart->format('Y-m-d H:i') === now()->addDay()->setTime(10, 0)->format('Y-m-d H:i'),
    );
});

test('customer can reschedule their own booking', function () {
    $customer = User::factory()->create(['role' => 'user']);
    $service = rescheduleService();
    $capster = rescheduleCapster();
    $booking = rescheduleBooking($customer, $service, $capster);

    Notification::fake();

    $this->actingAs($customer)
        ->patch(route('booking.reschedule', $booking), [
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '12:00',
        ])
        ->assertRedirect(route('booking.show', $booking));

    $booking->refresh();

    expect($booking->status)->toBe('PENDING')
        ->and($booking->booking_start->format('H:i'))->toBe('12:00');
});

test('customer cannot reschedule a booking belonging to another user', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $otherCustomer = User::factory()->create(['role' => 'user']);
    $service = rescheduleService();
    $capster = rescheduleCapster();
    $booking = rescheduleBooking($owner, $service, $capster);

    $this->actingAs($otherCustomer)
        ->patch(route('booking.reschedule', $booking), [
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '12:00',
        ])
        ->assertForbidden();

    expect($booking->refresh()->status)->toBe('PENDING');
});

test('reschedule is rejected when the new slot is already booked', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create();
    $service = rescheduleService();
    $capster = rescheduleCapster();
    $booking = rescheduleBooking($customer, $service, $capster);
    rescheduleBooking($customer, $service, $capster, [
        'booking_start' => now()->addDays(2)->setTime(11, 0),
        'booking_end' => now()->addDays(2)->setTime(12, 0),
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.reschedule', $booking), [
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '11:00',
        ])
        ->assertSessionHasErrors('booking_time');

    expect($booking->refresh()->booking_start->toDateString())->toBe(now()->addDay()->toDateString());
});

test('reschedule can reactivate an auto cancelled booking', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create();
    $service = rescheduleService();
    $capster = rescheduleCapster();
    $booking = rescheduleBooking($customer, $service, $capster, [
        'status' => 'AUTO_CANCELLED',
        'customer_response_deadline' => now()->subMinute(),
    ]);

    Notification::fake();

    $this->actingAs($admin)
        ->patch(route('admin.bookings.reschedule', $booking), [
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '11:00',
        ])
        ->assertRedirect(route('admin.bookings.show', $booking));

    expect($booking->refresh()->status)->toBe('PENDING');

    Notification::assertSentTo($customer, BookingRescheduledNotification::class);
});

test('a booking with a terminal status cannot be rescheduled', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create();
    $service = rescheduleService();
    $capster = rescheduleCapster();
    $booking = rescheduleBooking($customer, $service, $capster, [
        'status' => 'COMPLETED',
        'completed_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.bookings.reschedule.form', $booking))
        ->assertRedirect(route('admin.bookings.show', $booking));

    $this->actingAs($admin)
        ->patch(route('admin.bookings.reschedule', $booking), [
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '11:00',
        ])
        ->assertForbidden();

    expect($booking->refresh()->status)->toBe('COMPLETED');
});
