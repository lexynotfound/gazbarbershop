<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function reviewService(array $attributes = []): Service
{
    return Service::query()->create($attributes + [
        'name' => 'Cukur Rambut',
        'description' => 'Potongan presisi.',
        'price' => 40000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);
}

function reviewCapster(array $attributes = []): Capster
{
    return Capster::query()->create($attributes + [
        'name' => 'Rudi',
        'rating' => 4.9,
        'service_fee' => 50000,
        'is_active' => true,
        'description' => 'Specialist fade.',
    ]);
}

function reviewBooking(User $user, Service $service, Capster $capster, array $attributes = []): Booking
{
    $booking = Booking::query()->create($attributes + [
        'booking_code' => 'GAZ-'.fake()->unique()->bothify('??????'),
        'user_id' => $user->id,
        'capster_id' => $capster->id,
        'booking_start' => now()->subDay()->setTime(10, 0),
        'booking_end' => now()->subDay()->setTime(10, 30),
        'service_total' => $service->price,
        'capster_fee' => $capster->service_fee,
        'grand_total' => $service->price + $capster->service_fee,
        'status' => 'COMPLETED',
        'completed_at' => now()->subDay()->setTime(10, 30),
    ]);

    $booking->items()->create([
        'service_id' => $service->id,
        'price' => $service->price,
        'duration_minutes' => $service->duration_minutes,
    ]);

    return $booking;
}

test('user can choose only completed unreviewed bookings on review page', function () {
    $user = User::factory()->create(['role' => 'user']);
    $otherUser = User::factory()->create(['role' => 'user']);
    $service = reviewService();
    $capster = reviewCapster();
    $reviewableBooking = reviewBooking($user, $service, $capster, ['booking_code' => 'GAZ-REVIEWABLE']);
    $pendingBooking = reviewBooking($user, $service, $capster, [
        'booking_code' => 'GAZ-PENDING',
        'status' => 'PENDING',
        'completed_at' => null,
    ]);
    $otherBooking = reviewBooking($otherUser, $service, $capster, ['booking_code' => 'GAZ-OTHER']);
    $reviewedBooking = reviewBooking($user, $service, $capster, ['booking_code' => 'GAZ-REVIEWED']);

    Review::query()->create([
        'booking_id' => $reviewedBooking->id,
        'user_id' => $user->id,
        'capster_id' => $capster->id,
        'rating' => 5,
        'comment' => 'Sudah direview.',
    ]);

    $this->actingAs($user)
        ->get(route('booking.review'))
        ->assertSuccessful()
        ->assertSee('Pilih Booking untuk Direview')
        ->assertSee($reviewableBooking->booking_code)
        ->assertSee(route('booking.review', ['booking' => $reviewableBooking->id]), false)
        ->assertDontSee($pendingBooking->booking_code)
        ->assertDontSee($otherBooking->booking_code)
        ->assertDontSee($reviewedBooking->booking_code);
});

test('user can submit a review for their completed booking', function () {
    $user = User::factory()->create(['role' => 'user']);
    $service = reviewService();
    $capster = reviewCapster(['rating' => 3.0]);
    $previousBooking = reviewBooking(User::factory()->create(['role' => 'user']), $service, $capster);
    $booking = reviewBooking($user, $service, $capster, ['booking_code' => 'GAZ-SUBMIT']);

    Review::query()->create([
        'booking_id' => $previousBooking->id,
        'user_id' => $previousBooking->user_id,
        'capster_id' => $capster->id,
        'rating' => 3,
        'comment' => 'Cukup.',
    ]);

    $this->actingAs($user)
        ->post(route('booking.review.store'), [
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'bagus',
        ])
        ->assertRedirect(route('booking.review'))
        ->assertSessionHas('status', 'Ulasan berhasil dikirim. Terima kasih atas penilaiannya.');

    $this->assertDatabaseHas('reviews', [
        'booking_id' => $booking->id,
        'user_id' => $user->id,
        'capster_id' => $capster->id,
        'rating' => 5,
        'comment' => 'bagus',
    ]);

    expect($booking->refresh()->status)->toBe('REVIEWED')
        ->and((float) $capster->refresh()->rating)->toBe(4.0);
});

test('user cannot review a booking that is not completed', function () {
    $user = User::factory()->create(['role' => 'user']);
    $service = reviewService();
    $capster = reviewCapster();
    $booking = reviewBooking($user, $service, $capster, [
        'status' => 'PAID',
        'completed_at' => null,
    ]);

    $this->actingAs($user)
        ->post(route('booking.review.store'), [
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'bagus',
        ])
        ->assertSessionHasErrors('booking_id');

    expect(Review::query()->count())->toBe(0);
});

test('user cannot review another users booking', function () {
    $user = User::factory()->create(['role' => 'user']);
    $otherUser = User::factory()->create(['role' => 'user']);
    $service = reviewService();
    $capster = reviewCapster();
    $booking = reviewBooking($otherUser, $service, $capster);

    $this->actingAs($user)
        ->post(route('booking.review.store'), [
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'bagus',
        ])
        ->assertForbidden();

    expect(Review::query()->count())->toBe(0);
});

test('user cannot review the same booking twice', function () {
    $user = User::factory()->create(['role' => 'user']);
    $service = reviewService();
    $capster = reviewCapster();
    $booking = reviewBooking($user, $service, $capster);

    Review::query()->create([
        'booking_id' => $booking->id,
        'user_id' => $user->id,
        'capster_id' => $capster->id,
        'rating' => 4,
        'comment' => 'Review pertama.',
    ]);

    $this->actingAs($user)
        ->post(route('booking.review.store'), [
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'Review kedua.',
        ])
        ->assertSessionHasErrors('booking_id');

    expect(Review::query()->count())->toBe(1);
});

test('rating is required and must be between one and five', function () {
    $user = User::factory()->create(['role' => 'user']);
    $service = reviewService();
    $capster = reviewCapster();
    $booking = reviewBooking($user, $service, $capster);

    $this->actingAs($user)
        ->post(route('booking.review.store'), [
            'booking_id' => $booking->id,
            'rating' => 0,
            'comment' => 'bagus',
        ])
        ->assertSessionHasErrors('rating');

    expect(Review::query()->count())->toBe(0);
});
