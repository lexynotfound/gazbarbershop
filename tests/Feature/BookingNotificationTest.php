<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\Service;
use App\Models\User;
use App\Notifications\BookingCancelledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function notificationBooking(User $customer, string $status, array $attributes = []): Booking
{
    $capster = Capster::query()->create([
        'name' => 'Capster Notifikasi '.fake()->unique()->numerify('###'),
        'rating' => 4.8,
        'service_fee' => 30000,
        'is_active' => true,
        'description' => 'Capster pengujian notifikasi.',
    ]);
    $service = Service::query()->create([
        'name' => 'Layanan Notifikasi '.fake()->unique()->numerify('###'),
        'description' => 'Layanan pengujian notifikasi.',
        'price' => 50000,
        'duration_minutes' => 60,
        'is_active' => true,
    ]);
    $booking = Booking::query()->create($attributes + [
        'booking_code' => 'GAZ-NOTIF-'.fake()->unique()->numerify('######'),
        'user_id' => $customer->id,
        'capster_id' => $capster->id,
        'booking_start' => now()->addHour(),
        'booking_end' => now()->addHours(2),
        'service_total' => $service->price,
        'capster_fee' => $capster->service_fee,
        'grand_total' => $service->price + $capster->service_fee,
        'status' => $status,
    ]);

    $booking->items()->create([
        'service_id' => $service->id,
        'price' => $service->price,
        'duration_minutes' => $service->duration_minutes,
    ]);

    return $booking;
}

test('auto cancel command queues email and database notifications with cancellation reasons', function () {
    $customer = User::factory()->create(['role' => 'user']);
    $unconfirmed = notificationBooking($customer, 'WAITING_CUSTOMER_CONFIRMATION', [
        'customer_response_deadline' => now()->subMinute(),
    ]);
    $late = notificationBooking($customer, 'CONFIRMED', [
        'booking_start' => now()->subMinutes(16),
        'booking_end' => now()->addMinutes(44),
    ]);
    $recent = notificationBooking($customer, 'CONFIRMED', [
        'booking_start' => now()->subMinutes(14),
        'booking_end' => now()->addMinutes(46),
    ]);

    Notification::fake();

    $this->artisan('app:cancel-late-bookings')->assertSuccessful();

    expect($unconfirmed->refresh()->status)->toBe('AUTO_CANCELLED')
        ->and($late->refresh()->status)->toBe('LATE_CANCELLED')
        ->and($recent->refresh()->status)->toBe('CONFIRMED');

    Notification::assertSentTo(
        $customer,
        BookingCancelledNotification::class,
        fn (BookingCancelledNotification $notification): bool => $notification->booking->is($unconfirmed)
            && $notification->reason === 'NO_CONFIRMATION',
    );
    Notification::assertSentTo(
        $customer,
        BookingCancelledNotification::class,
        fn (BookingCancelledNotification $notification): bool => $notification->booking->is($late)
            && $notification->reason === 'LATE_ARRIVAL',
    );
});

test('database cancellation notification is stored and shown on user dashboard', function () {
    $customer = User::factory()->create(['role' => 'user']);
    $booking = notificationBooking($customer, 'AUTO_CANCELLED');
    $notification = new BookingCancelledNotification($booking->load('capster'), 'NO_CONFIRMATION');

    $customer->notifyNow($notification, ['database']);

    expect($customer->notifications()->count())->toBe(1)
        ->and($customer->notifications()->first()->data['booking_code'])->toBe($booking->booking_code)
        ->and($customer->notifications()->first()->data['reason'])->toBe('NO_CONFIRMATION');

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Notifikasi Terbaru')
        ->assertSee('Booking dibatalkan otomatis')
        ->assertSee('Reschedule Booking')
        ->assertSee(route('booking.reschedule.form', $booking), false);
});
