<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin reviews page lists customer reviews', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create([
        'name' => 'Rizky Reviewer',
        'email' => 'rizky.reviewer@example.com',
    ]);
    $service = Service::query()->create([
        'name' => 'Cukur Rambut',
        'description' => 'Potongan presisi.',
        'price' => 40000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);
    $capster = Capster::query()->create([
        'name' => 'Rudi',
        'rating' => 4.9,
        'service_fee' => 50000,
        'is_active' => true,
        'description' => 'Specialist fade.',
    ]);
    $booking = Booking::query()->create([
        'booking_code' => 'GAZ-REVIEW-1',
        'user_id' => $customer->id,
        'capster_id' => $capster->id,
        'booking_start' => now()->subDay()->setTime(10, 0),
        'booking_end' => now()->subDay()->setTime(10, 30),
        'service_total' => $service->price,
        'capster_fee' => $capster->service_fee,
        'grand_total' => $service->price + $capster->service_fee,
        'status' => 'COMPLETED',
    ]);
    $booking->items()->create([
        'service_id' => $service->id,
        'price' => $service->price,
        'duration_minutes' => $service->duration_minutes,
    ]);

    $review = Review::query()->create([
        'booking_id' => $booking->id,
        'user_id' => $customer->id,
        'capster_id' => $capster->id,
        'rating' => 5,
        'comment' => 'Pelayanan sangat rapi.',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reviews.index'))
        ->assertSuccessful()
        ->assertSee('Review')
        ->assertSee('Total Review')
        ->assertSee('Rata-rata Rating')
        ->assertSee('Rizky Reviewer')
        ->assertSee('rizky.reviewer@example.com')
        ->assertSee('GAZ-REVIEW-1')
        ->assertSee('Rudi')
        ->assertSee('5/5')
        ->assertSee('Pelayanan sangat rapi.')
        ->assertSee(route('admin.reviews.show', $review), false)
        ->assertSee('Detail');
});

test('admin can open review detail page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create([
        'name' => 'Member Demo',
        'email' => 'user@gazbarbershop.com',
        'phone' => '6281234567002',
    ]);
    $service = Service::query()->create([
        'name' => 'Cukur Rambut',
        'description' => 'Potongan presisi.',
        'price' => 40000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);
    $capster = Capster::query()->create([
        'name' => 'Rudi',
        'rating' => 4.9,
        'service_fee' => 50000,
        'is_active' => true,
        'description' => 'Specialist fade.',
    ]);
    $booking = Booking::query()->create([
        'booking_code' => 'GAZ-260531-001',
        'user_id' => $customer->id,
        'capster_id' => $capster->id,
        'booking_start' => now()->subDay()->setTime(10, 0),
        'booking_end' => now()->subDay()->setTime(10, 30),
        'service_total' => $service->price,
        'capster_fee' => $capster->service_fee,
        'grand_total' => $service->price + $capster->service_fee,
        'status' => 'COMPLETED',
    ]);
    $booking->items()->create([
        'service_id' => $service->id,
        'price' => $service->price,
        'duration_minutes' => $service->duration_minutes,
    ]);
    $review = Review::query()->create([
        'booking_id' => $booking->id,
        'user_id' => $customer->id,
        'capster_id' => $capster->id,
        'rating' => 5,
        'comment' => 'Pelayanan bagus dan hasil memuaskan.',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reviews.show', $review))
        ->assertSuccessful()
        ->assertSee('Detail Review')
        ->assertSee(route('admin.reviews.index'), false)
        ->assertSee(route('admin.bookings.show', $booking), false)
        ->assertSee('Member Demo')
        ->assertSee('user@gazbarbershop.com')
        ->assertSee('6281234567002')
        ->assertSee('GAZ-260531-001')
        ->assertSee('Rudi')
        ->assertSee('Cukur Rambut')
        ->assertSee('Rp90.000')
        ->assertSee('5/5')
        ->assertSee('Pelayanan bagus dan hasil memuaskan.');
});

test('admin sidebar review links to reviews page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee(route('admin.reviews.index'), false);
});
