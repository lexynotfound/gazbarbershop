<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function transactionAdmin(array $attributes = []): User
{
    return User::factory()->create($attributes + [
        'role' => 'admin',
    ]);
}

function transactionUser(array $attributes = []): User
{
    return User::factory()->create($attributes + [
        'role' => 'user',
    ]);
}

function transactionService(array $attributes = []): Service
{
    return Service::query()->create($attributes + [
        'name' => 'Cukur Rambut + Cukur + Cuci',
        'description' => 'Paket lengkap.',
        'price' => 100000,
        'duration_minutes' => 60,
        'is_active' => true,
    ]);
}

function transactionCapster(array $attributes = []): Capster
{
    return Capster::query()->create($attributes + [
        'name' => 'Rudi',
        'rating' => 4.9,
        'service_fee' => 50000,
        'is_active' => true,
        'description' => 'Specialist.',
    ]);
}

function transactionBooking(array $bookingAttributes = [], array $paymentAttributes = []): Payment
{
    $user = $bookingAttributes['user'] ?? transactionUser(['name' => 'Member Demo']);
    $service = $bookingAttributes['service'] ?? transactionService();
    $capster = $bookingAttributes['capster'] ?? transactionCapster();

    unset($bookingAttributes['user'], $bookingAttributes['service'], $bookingAttributes['capster']);

    $booking = Booking::query()->create($bookingAttributes + [
        'booking_code' => 'GAZ-'.fake()->unique()->numerify('######-###'),
        'user_id' => $user->id,
        'capster_id' => $capster->id,
        'booking_start' => now()->setTime(10, 0),
        'booking_end' => now()->setTime(11, 0),
        'service_total' => $service->price,
        'capster_fee' => $capster->service_fee,
        'grand_total' => $service->price + $capster->service_fee,
        'status' => 'PAID',
    ]);

    $booking->items()->create([
        'service_id' => $service->id,
        'price' => $service->price,
        'duration_minutes' => $service->duration_minutes,
    ]);

    return Payment::query()->create($paymentAttributes + [
        'booking_id' => $booking->id,
        'amount' => $booking->grand_total,
        'method' => 'cash',
        'status' => 'paid',
        'paid_at' => now(),
    ]);
}

test('admin can open transaction report with sidebar link and summary', function () {
    $admin = transactionAdmin();

    transactionBooking([
        'booking_code' => 'GAZ-260531-001',
    ], [
        'amount' => 150000,
        'method' => 'cash',
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.transactions.index'))
        ->assertSuccessful()
        ->assertSee('Laporan Transaksi')
        ->assertSee('Transaksi')
        ->assertSee(route('admin.transactions.index'), false)
        ->assertSee('GAZ-260531-001')
        ->assertSee('Member Demo')
        ->assertSee('Cukur Rambut + Cukur + Cuci')
        ->assertSee('Rudi')
        ->assertSee('Rp150.000')
        ->assertSee('Cash');
});

test('regular user cannot access transaction report', function () {
    $this->actingAs(transactionUser())
        ->get(route('admin.transactions.index'))
        ->assertForbidden();
});

test('transaction report shows paid revenue and unpaid amount for active period', function () {
    $admin = transactionAdmin();

    transactionBooking([], [
        'amount' => 150000,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    transactionBooking([
        'booking_code' => 'GAZ-UNPAID-001',
    ], [
        'amount' => 50000,
        'status' => 'unpaid',
        'paid_at' => null,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.transactions.index'))
        ->assertSuccessful()
        ->assertSee('Pendapatan Lunas')
        ->assertSee('Rp150.000')
        ->assertSee('Belum Dibayar')
        ->assertSee('Rp50.000');
});

test('transaction report filters by status method and date', function () {
    $admin = transactionAdmin();

    transactionBooking([
        'booking_code' => 'GAZ-CASH-001',
    ], [
        'amount' => 150000,
        'method' => 'cash',
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    transactionBooking([
        'booking_code' => 'GAZ-QRIS-001',
    ], [
        'amount' => 200000,
        'method' => 'qris',
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    transactionBooking([
        'booking_code' => 'GAZ-OLD-001',
    ], [
        'amount' => 300000,
        'method' => 'cash',
        'status' => 'paid',
        'paid_at' => now()->subMonths(2),
        'created_at' => now()->subMonths(2),
        'updated_at' => now()->subMonths(2),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.transactions.index', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'status' => 'paid',
            'method' => 'cash',
        ]))
        ->assertSuccessful()
        ->assertSee('GAZ-CASH-001')
        ->assertDontSee('GAZ-QRIS-001')
        ->assertDontSee('GAZ-OLD-001');
});

test('admin can export filtered transaction report as csv', function () {
    $admin = transactionAdmin();

    transactionBooking([
        'booking_code' => 'GAZ-CSV-001',
    ], [
        'amount' => 150000,
        'method' => 'cash',
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.transactions.export.csv', [
        'start_date' => now()->startOfMonth()->toDateString(),
        'end_date' => now()->endOfMonth()->toDateString(),
        'status' => 'paid',
        'method' => 'cash',
    ]));

    $response->assertSuccessful()->assertStreamed();

    $content = $response->streamedContent();

    expect($response->headers->get('content-disposition'))->toContain('laporan-transaksi')
        ->and($content)->toContain('"Tanggal Bayar","Kode Booking",Customer,Layanan,Capster,Metode,Status,Nominal')
        ->and($content)->toContain('GAZ-CSV-001')
        ->and($content)->toContain('Member Demo')
        ->and($content)->toContain('Cash,Lunas,150000');
});

test('admin can export filtered transaction report as pdf', function () {
    $admin = transactionAdmin();

    transactionBooking([
        'booking_code' => 'GAZ-PDF-001',
    ], [
        'amount' => 150000,
        'method' => 'cash',
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.transactions.export.pdf', [
        'start_date' => now()->startOfMonth()->toDateString(),
        'end_date' => now()->endOfMonth()->toDateString(),
        'status' => 'paid',
        'method' => 'cash',
    ]));

    $response->assertSuccessful();

    expect($response->headers->get('content-type'))->toContain('application/pdf')
        ->and($response->headers->get('content-disposition'))->toContain('laporan-transaksi');
});
