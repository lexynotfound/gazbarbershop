<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function customerService(array $attributes = []): Service
{
    return Service::query()->create($attributes + [
        'name' => 'Cukur Rambut',
        'description' => 'Potongan presisi.',
        'price' => 40000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);
}

function customerCapster(array $attributes = []): Capster
{
    return Capster::query()->create($attributes + [
        'name' => 'Rudi',
        'rating' => 4.9,
        'service_fee' => 50000,
        'is_active' => true,
        'description' => 'Specialist fade.',
    ]);
}

function customerBooking(User $user, Service $service, Capster $capster, string $status = 'PENDING'): Booking
{
    return Booking::query()->create([
        'booking_code' => 'GAZ-CUSTOMER-'.fake()->unique()->numerify('######'),
        'user_id' => $user->id,
        'capster_id' => $capster->id,
        'booking_start' => now()->addDay()->setTime(10, 0),
        'booking_end' => now()->addDay()->setTime(10, 30),
        'service_total' => $service->price,
        'capster_fee' => $capster->service_fee,
        'grand_total' => $service->price + $capster->service_fee,
        'status' => $status,
    ]);
}

test('admin customers page lists only customer users', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create([
        'name' => 'Rizky Pelanggan',
        'email' => 'rizky@example.com',
        'phone' => '628123456789',
        'role' => 'user',
    ]);
    $otherCustomer = User::factory()->create([
        'name' => 'Dina Pelanggan',
        'role' => 'user',
    ]);
    $service = customerService();
    $capster = customerCapster();

    customerBooking($customer, $service, $capster);

    $this->actingAs($admin)
        ->get(route('admin.customers.index'))
        ->assertSuccessful()
        ->assertSee('Pelanggan')
        ->assertSee('2 Pelanggan')
        ->assertSee('Rizky Pelanggan')
        ->assertSee('rizky@example.com')
        ->assertSee('628123456789')
        ->assertSee('0 booking')
        ->assertSee('Belum repeat')
        ->assertSee('Dina Pelanggan')
        ->assertDontSee($admin->email);
});

test('admin sidebar pelanggan links to customers page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee(route('admin.customers.index'), false);
});

test('admin customers page marks repeat customers from completed bookings only', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $repeatCustomer = User::factory()->create([
        'name' => 'Loyal Customer',
        'phone' => '628123456789',
        'role' => 'user',
    ]);
    $regularCustomer = User::factory()->create([
        'name' => 'Regular Customer',
        'phone' => '628987654321',
        'role' => 'user',
    ]);
    $service = customerService();
    $capster = customerCapster();

    customerBooking($repeatCustomer, $service, $capster, 'COMPLETED');
    customerBooking($repeatCustomer, $service, $capster, 'COMPLETED');
    customerBooking($repeatCustomer, $service, $capster, 'COMPLETED');
    customerBooking($regularCustomer, $service, $capster, 'COMPLETED');
    customerBooking($regularCustomer, $service, $capster, 'PENDING');
    customerBooking($regularCustomer, $service, $capster, 'REJECTED');

    $this->actingAs($admin)
        ->get(route('admin.customers.index'))
        ->assertSuccessful()
        ->assertSee('Loyal Customer')
        ->assertSee('3 booking')
        ->assertSee('Repeat')
        ->assertSee('Kirim Promo')
        ->assertSee(route('admin.customers.promo-whatsapp', $repeatCustomer), false)
        ->assertSee('Regular Customer')
        ->assertSee('1 booking')
        ->assertSee('Belum memenuhi syarat');
});

test('admin can open whatsapp promo for repeat customer', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create([
        'name' => 'Rizky Loyal',
        'phone' => '+62 812-3456-789',
        'role' => 'user',
    ]);
    $service = customerService();
    $capster = customerCapster();

    customerBooking($customer, $service, $capster, 'COMPLETED');
    customerBooking($customer, $service, $capster, 'COMPLETED');
    customerBooking($customer, $service, $capster, 'COMPLETED');

    $message = implode("\n", [
        'Halo Rizky Loyal, terima kasih sudah sering booking di GAZ Barbershop.',
        '',
        'Sebagai pelanggan loyal, kami punya promo spesial untuk kunjungan berikutnya.',
        'Silakan booking kembali dan tunjukkan pesan ini saat datang.',
        '',
        'Terima kasih.',
        'GAZ Barbershop',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.customers.promo-whatsapp', $customer))
        ->assertRedirect('https://wa.me/628123456789?text='.urlencode($message));
});

test('admin cannot send promo whatsapp to non repeat customer', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create([
        'name' => 'Rizky Baru',
        'phone' => '628123456789',
        'role' => 'user',
    ]);
    $service = customerService();
    $capster = customerCapster();

    customerBooking($customer, $service, $capster, 'COMPLETED');
    customerBooking($customer, $service, $capster, 'PENDING');

    $this->actingAs($admin)
        ->get(route('admin.customers.promo-whatsapp', $customer))
        ->assertRedirect(route('admin.customers.index'))
        ->assertSessionHas('status', 'Promo hanya tersedia untuk pelanggan repeat order.');
});
