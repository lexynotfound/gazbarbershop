<?php

use App\Models\Capster;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function fakeServiceImage(string $name = 'service.png'): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'service-image-');

    file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));

    return new UploadedFile($path, $name, 'image/png', null, true);
}

test('admin can create a service with uploaded image', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.services.store'), [
            'name' => 'Creambath Premium',
            'image' => fakeServiceImage('creambath.png'),
            'price' => 85000,
            'duration_minutes' => 60,
            'is_active' => '1',
            'description' => 'Treatment rambut premium.',
        ])
        ->assertRedirect(route('admin.services.index'))
        ->assertSessionHasNoErrors();

    $service = Service::query()->where('name', 'Creambath Premium')->firstOrFail();

    expect($service->image)->not->toBeNull()
        ->and($service->price)->toBe(85000)
        ->and($service->duration_minutes)->toBe(60)
        ->and($service->is_active)->toBeTrue();

    Storage::disk('public')->assertExists($service->image);
});

test('admin can update a service and replace its image', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $oldImage = 'services/old.png';
    Storage::disk('public')->put($oldImage, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));
    $service = Service::query()->create([
        'name' => 'Cukur Lama',
        'description' => 'Deskripsi lama.',
        'image' => $oldImage,
        'price' => 40000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.services.update', $service), [
            'name' => 'Cukur Modern',
            'image' => fakeServiceImage('new.png'),
            'price' => 55000,
            'duration_minutes' => 45,
            'is_active' => '0',
            'description' => 'Deskripsi baru.',
        ])
        ->assertRedirect(route('admin.services.index'))
        ->assertSessionHasNoErrors();

    $service->refresh();

    expect($service->name)->toBe('Cukur Modern')
        ->and($service->price)->toBe(55000)
        ->and($service->duration_minutes)->toBe(45)
        ->and($service->is_active)->toBeFalse()
        ->and($service->image)->not->toBe($oldImage);

    Storage::disk('public')->assertMissing($oldImage);
    Storage::disk('public')->assertExists($service->image);
});

test('services pages use database services and show active services publicly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $activeService = Service::query()->create([
        'name' => 'Hair Spa',
        'description' => 'Perawatan rambut.',
        'image' => 'services/hair-spa.jpg',
        'price' => 90000,
        'duration_minutes' => 60,
        'is_active' => true,
    ]);
    $inactiveService = Service::query()->create([
        'name' => 'Layanan Arsip',
        'description' => 'Tidak tampil publik.',
        'price' => 10000,
        'duration_minutes' => 15,
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.services.index'))
        ->assertSuccessful()
        ->assertSee('Hair Spa')
        ->assertSee('Layanan Arsip')
        ->assertSee(route('admin.services.edit', $activeService), false)
        ->assertSee(route('admin.services.edit', $inactiveService), false);

    $this->get(route('services'))
        ->assertSuccessful()
        ->assertSee('Hair Spa')
        ->assertSee('services/hair-spa.jpg', false)
        ->assertDontSee('Layanan Arsip');
});

test('home page shows database images for services and capsters with fallbacks', function () {
    Service::query()->create([
        'name' => 'Layanan Bergambar',
        'description' => 'Punya gambar.',
        'image' => 'services/layanan-bergambar.jpg',
        'price' => 120000,
        'duration_minutes' => 75,
        'is_active' => true,
    ]);
    Service::query()->create([
        'name' => 'Layanan Tanpa Gambar',
        'description' => 'Belum ada gambar.',
        'price' => 50000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);
    Capster::query()->create([
        'name' => 'Rafi',
        'photo' => 'capsters/rafi.jpg',
        'rating' => 4.9,
        'service_fee' => 65000,
        'is_active' => true,
    ]);
    Capster::query()->create([
        'name' => 'Tono',
        'rating' => 4.8,
        'service_fee' => 45000,
        'is_active' => true,
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Layanan Bergambar')
        ->assertSee('services/layanan-bergambar.jpg', false)
        ->assertSee('Layanan Tanpa Gambar')
        ->assertSee('✂')
        ->assertSee('Rafi')
        ->assertSee('capsters/rafi.jpg', false)
        ->assertSee('Tono')
        ->assertSee('T');
});

test('admin can delete a service without image', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $service = Service::query()->create([
        'name' => 'Cukur Biasa',
        'description' => 'Potongan standard.',
        'price' => 35000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.services.destroy', $service))
        ->assertRedirect(route('admin.services.index'))
        ->assertSessionHas('status', 'Layanan berhasil dihapus.');

    $this->assertDatabaseMissing('services', ['id' => $service->id]);
});

test('admin can delete a service with image and the file is removed from storage', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $image = 'services/cukur.png';
    Storage::disk('public')->put($image, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));
    $service = Service::query()->create([
        'name' => 'Cukur Bergambar',
        'description' => 'Punya gambar.',
        'image' => $image,
        'price' => 40000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.services.destroy', $service))
        ->assertRedirect(route('admin.services.index'));

    $this->assertDatabaseMissing('services', ['id' => $service->id]);
    Storage::disk('public')->assertMissing($image);
});

test('services index shows delete button for each service', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $service = Service::query()->create([
        'name' => 'Hair Styling',
        'description' => 'Penataan rambut.',
        'price' => 60000,
        'duration_minutes' => 45,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.services.index'))
        ->assertSuccessful()
        ->assertSee(route('admin.services.destroy', $service), false);
});

test('regular users cannot delete services', function () {
    $user = User::factory()->create(['role' => 'user']);
    $service = Service::query()->create([
        'name' => 'Cukur Biasa',
        'description' => 'Potongan standard.',
        'price' => 35000,
        'duration_minutes' => 30,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->delete(route('admin.services.destroy', $service))
        ->assertForbidden();

    $this->assertDatabaseHas('services', ['id' => $service->id]);
});

test('capster page shows active database photos with initial fallback', function () {
    Capster::query()->create([
        'name' => 'Rafi',
        'photo' => 'capsters/rafi.jpg',
        'rating' => 4.9,
        'service_fee' => 65000,
        'is_active' => true,
    ]);
    Capster::query()->create([
        'name' => 'Tono',
        'rating' => 4.8,
        'service_fee' => 45000,
        'is_active' => true,
    ]);
    Capster::query()->create([
        'name' => 'Capster Nonaktif',
        'photo' => 'capsters/nonaktif.jpg',
        'rating' => 5.0,
        'service_fee' => 75000,
        'is_active' => false,
    ]);

    $this->get(route('capsters'))
        ->assertSuccessful()
        ->assertSee('Capster Profesional')
        ->assertSee('Rafi')
        ->assertSee('capsters/rafi.jpg', false)
        ->assertSee('Tono')
        ->assertSee('T')
        ->assertDontSee('Capster Nonaktif')
        ->assertDontSee('capsters/nonaktif.jpg', false);
});
