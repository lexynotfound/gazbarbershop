<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function showCapster(array $attributes = []): Capster
{
    return Capster::query()->create($attributes + [
        'name' => 'Budi Santoso',
        'rating' => 4.8,
        'service_fee' => 50000,
        'is_active' => true,
    ]);
}

function showBookingForReview(User $user, Capster $capster): Booking
{
    return Booking::query()->create([
        'booking_code' => 'GAZ-'.fake()->unique()->bothify('??????'),
        'user_id' => $user->id,
        'capster_id' => $capster->id,
        'booking_start' => now()->subDay()->setTime(10, 0),
        'booking_end' => now()->subDay()->setTime(10, 30),
        'service_total' => 40000,
        'capster_fee' => 50000,
        'grand_total' => 90000,
        'status' => 'REVIEWED',
    ]);
}

test('active capster detail page returns 200 and shows capster name', function () {
    $capster = showCapster(['name' => 'Budi Santoso']);

    $this->get(route('capster.show', $capster))
        ->assertOk()
        ->assertSee('Budi Santoso');
});

test('inactive capster detail page returns 404', function () {
    $capster = showCapster(['is_active' => false]);

    $this->get(route('capster.show', $capster))
        ->assertNotFound();
});

test('capster detail page shows reviews from users', function () {
    $capster = showCapster(['name' => 'Ardi']);
    $user = User::factory()->create(['name' => 'Pelanggan A', 'role' => 'user']);
    $booking = showBookingForReview($user, $capster);

    Review::query()->create([
        'booking_id' => $booking->id,
        'user_id' => $user->id,
        'capster_id' => $capster->id,
        'rating' => 5,
        'comment' => 'Hasilnya bagus sekali!',
    ]);

    $this->get(route('capster.show', $capster))
        ->assertOk()
        ->assertSee('Pelanggan A')
        ->assertSee('Hasilnya bagus sekali!')
        ->assertSee('5/5');
});

test('capster detail page shows empty state when no reviews exist', function () {
    $capster = showCapster();

    $this->get(route('capster.show', $capster))
        ->assertOk()
        ->assertSee('Belum ada review untuk capster ini.');
});

test('capster cards on home page link to capster detail page', function () {
    $capster = showCapster(['name' => 'Fadli', 'rating' => 4.9]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('capster.show', $capster), false);
});
