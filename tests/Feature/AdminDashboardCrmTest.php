<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crmService(array $attributes = []): Service
{
    return Service::query()->create($attributes + [
        'name' => 'Haircut CRM',
        'description' => 'Layanan untuk pengujian CRM.',
        'price' => 75000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);
}

function crmCapster(array $attributes = []): Capster
{
    return Capster::query()->create($attributes + [
        'name' => 'Rian CRM',
        'rating' => 0,
        'service_fee' => 25000,
        'is_active' => true,
        'description' => 'Capster untuk pengujian CRM.',
    ]);
}

function crmBooking(
    User $customer,
    Service $service,
    Capster $capster,
    string $status,
    CarbonInterface $bookingStart,
    ?int $rating = null,
): Booking {
    $booking = Booking::query()->create([
        'booking_code' => 'CRM-'.fake()->unique()->numerify('########'),
        'user_id' => $customer->id,
        'capster_id' => $capster->id,
        'booking_start' => $bookingStart,
        'booking_end' => $bookingStart->copy()->addMinutes(30),
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

    if ($rating !== null) {
        Review::query()->create([
            'booking_id' => $booking->id,
            'user_id' => $customer->id,
            'capster_id' => $capster->id,
            'rating' => $rating,
            'comment' => 'Pelayanan bagus.',
        ]);
    }

    return $booking;
}

test('crm report is added without removing dashboard statistics and confirmation actions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user', 'name' => 'Pelanggan Konfirmasi CRM']);
    $service = crmService();
    $capster = crmCapster();
    $pendingBooking = crmBooking($customer, $service, $capster, 'PENDING', now()->addDay());

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee('Total Booking')
        ->assertSee('Booking Hari Ini')
        ->assertSee('Capster')
        ->assertSee('Pelanggan')
        ->assertSee('Booking Terbaru')
        ->assertSee('Booking Menunggu Konfirmasi')
        ->assertSee('Ringkasan Laporan CRM')
        ->assertSee('Tandai WA Terkirim')
        ->assertSee('WhatsApp')
        ->assertSee(route('admin.bookings.confirm', $pendingBooking), false)
        ->assertSee(route('admin.bookings.whatsapp', $pendingBooking), false);
});

test('crm report counts active and repeat customers from completed and reviewed bookings', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $repeatCustomer = User::factory()->create(['role' => 'user', 'name' => 'Pelanggan Loyal CRM']);
    $activeCustomer = User::factory()->create(['role' => 'user', 'name' => 'Pelanggan Aktif CRM']);
    $excludedCustomer = User::factory()->create(['role' => 'user', 'name' => 'Pelanggan Ditolak CRM']);
    $service = crmService();
    $capster = crmCapster();

    crmBooking($repeatCustomer, $service, $capster, 'COMPLETED', now()->subMonths(2));
    crmBooking($repeatCustomer, $service, $capster, 'COMPLETED', now()->subMonth());
    crmBooking($repeatCustomer, $service, $capster, 'REVIEWED', now()->startOfMonth()->addDays(2), 5);
    crmBooking($activeCustomer, $service, $capster, 'COMPLETED', now()->startOfMonth()->addDays(3));
    crmBooking($excludedCustomer, $service, $capster, 'REJECTED', now()->startOfMonth()->addDays(4));

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertViewHas('crmReport', function (array $report): bool {
            return $report['activeCustomersCount'] === 2
                && $report['repeatCustomersCount'] === 1
                && $report['favoriteService'] === ['name' => 'Haircut CRM', 'transactionCount' => 2]
                && $report['favoriteCapster'] === ['name' => 'Rian CRM', 'bookingCount' => 2, 'averageRating' => 5.0]
                && $report['customers'][0]['name'] === 'Pelanggan Loyal CRM'
                && $report['customers'][0]['completedBookingsCount'] === 3
                && $report['customers'][0]['status'] === 'Repeat';
        })
        ->assertSee('Pelanggan Loyal CRM')
        ->assertSee('Pelanggan Aktif CRM')
        ->assertSee('Haircut CRM')
        ->assertSee('Rian CRM')
        ->assertSee('1 pelanggan repeat order (minimal 3 booking selesai) aktif kembali.')
        ->assertDontSee('Pelanggan Ditolak CRM');
});

test('crm month filter scopes the report and invalid values fall back to the current month', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user']);
    $capster = crmCapster();
    $previousService = crmService(['name' => 'Layanan Bulan Lalu']);
    $currentService = crmService(['name' => 'Layanan Bulan Ini']);

    crmBooking($customer, $previousService, $capster, 'COMPLETED', now()->subMonth()->startOfMonth()->addDay());
    crmBooking($customer, $currentService, $capster, 'COMPLETED', now()->startOfMonth()->addDay());

    $this->actingAs($admin)
        ->get(route('admin.dashboard', ['month' => now()->subMonth()->format('Y-m')]))
        ->assertSuccessful()
        ->assertViewHas('crmReport', fn (array $report): bool => $report['month'] === now()->subMonth()->format('Y-m')
            && $report['favoriteService']['name'] === 'Layanan Bulan Lalu'
            && $report['favoriteService']['transactionCount'] === 1);

    $this->actingAs($admin)
        ->get(route('admin.dashboard', ['month' => 'bulan-invalid']))
        ->assertSuccessful()
        ->assertViewHas('crmReport', fn (array $report): bool => $report['month'] === now()->format('Y-m')
            && $report['favoriteService']['name'] === 'Layanan Bulan Ini');
});
