<?php

use App\Models\Booking;
use App\Models\Capster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function fakePngUpload(string $name): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'capster-photo-');

    file_put_contents(
        $path,
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
    );

    return new UploadedFile($path, $name, 'image/png', null, true);
}

test('admin capster create form has cancel button and multipart upload', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.capsters.create'))
        ->assertSuccessful()
        ->assertSee('Tambah Capster')
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee(route('admin.capsters.index'), false)
        ->assertSee('Batal');
});

test('admin can create capster with uploaded photo', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $photo = fakePngUpload('rudi.png');

    $this->actingAs($admin)
        ->post(route('admin.capsters.store'), [
            'name' => 'Rudi',
            'photo' => $photo,
            'service_fee' => 50000,
            'is_active' => '1',
            'description' => 'Specialist fade dan gentleman cut.',
        ])
        ->assertRedirect(route('admin.capsters.index'));

    $capster = Capster::query()->where('name', 'Rudi')->firstOrFail();

    expect($capster->photo)->not->toBeNull();
    Storage::disk('public')->assertExists($capster->photo);
});

test('admin can update capster photo and cancel from edit form', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $oldPhoto = 'capsters/old.png';
    Storage::disk('public')->put($oldPhoto, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));
    $capster = Capster::query()->create([
        'name' => 'Rudi',
        'photo' => $oldPhoto,
        'rating' => 4.9,
        'service_fee' => 50000,
        'is_active' => true,
        'description' => 'Specialist fade.',
    ]);
    $newPhoto = fakePngUpload('new.png');

    $this->actingAs($admin)
        ->get(route('admin.capsters.edit', $capster))
        ->assertSuccessful()
        ->assertSee('Edit Capster')
        ->assertSee('Batal')
        ->assertSee(route('admin.capsters.index'), false);

    $this->actingAs($admin)
        ->patch(route('admin.capsters.update', $capster), [
            'name' => 'Rudi Pratama',
            'photo' => $newPhoto,
            'service_fee' => 60000,
            'is_active' => '0',
            'description' => 'Senior capster.',
        ])
        ->assertRedirect(route('admin.capsters.index'));

    $capster->refresh();

    expect($capster->name)->toBe('Rudi Pratama')
        ->and($capster->service_fee)->toBe(60000)
        ->and($capster->is_active)->toBeFalse()
        ->and($capster->photo)->not->toBe($oldPhoto);

    Storage::disk('public')->assertMissing($oldPhoto);
    Storage::disk('public')->assertExists($capster->photo);
});

test('admin can delete a capster without photo', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $capster = Capster::query()->create([
        'name' => 'Budi',
        'rating' => 4.5,
        'service_fee' => 40000,
        'is_active' => true,
        'description' => 'Capster biasa.',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.capsters.destroy', $capster))
        ->assertRedirect(route('admin.capsters.index'))
        ->assertSessionHas('status', 'Capster berhasil dihapus.');

    $this->assertDatabaseMissing('capsters', ['id' => $capster->id]);
});

test('admin can delete a capster with photo and the file is removed from storage', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $photo = 'capsters/budi.png';
    Storage::disk('public')->put($photo, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));
    $capster = Capster::query()->create([
        'name' => 'Budi',
        'photo' => $photo,
        'rating' => 4.5,
        'service_fee' => 40000,
        'is_active' => true,
        'description' => 'Capster dengan foto.',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.capsters.destroy', $capster))
        ->assertRedirect(route('admin.capsters.index'));

    $this->assertDatabaseMissing('capsters', ['id' => $capster->id]);
    Storage::disk('public')->assertMissing($photo);
});

test('capster index shows delete button for each capster', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $capster = Capster::query()->create([
        'name' => 'Andi',
        'rating' => 4.7,
        'service_fee' => 50000,
        'is_active' => true,
        'description' => 'Capster senior.',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.capsters.index'))
        ->assertSuccessful()
        ->assertSee(route('admin.capsters.destroy', $capster), false);
});

test('admin cannot delete a capster that has bookings', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'user']);
    $capster = Capster::query()->create([
        'name' => 'Budi',
        'rating' => 4.5,
        'service_fee' => 40000,
        'is_active' => true,
        'description' => 'Capster dengan booking.',
    ]);
    Booking::query()->create([
        'booking_code' => 'GAZ-TEST-DEL',
        'user_id' => $customer->id,
        'capster_id' => $capster->id,
        'booking_start' => now()->addDay(),
        'booking_end' => now()->addDay()->addHour(),
        'service_total' => 50000,
        'capster_fee' => 40000,
        'grand_total' => 90000,
        'status' => 'PENDING',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.capsters.destroy', $capster))
        ->assertRedirect(route('admin.capsters.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('capsters', ['id' => $capster->id]);
});

test('regular users cannot delete capsters', function () {
    $user = User::factory()->create(['role' => 'user']);
    $capster = Capster::query()->create([
        'name' => 'Budi',
        'rating' => 4.5,
        'service_fee' => 40000,
        'is_active' => true,
        'description' => 'Capster biasa.',
    ]);

    $this->actingAs($user)
        ->delete(route('admin.capsters.destroy', $capster))
        ->assertForbidden();

    $this->assertDatabaseHas('capsters', ['id' => $capster->id]);
});

test('legacy capster edit url redirects to first capster edit page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $capster = Capster::query()->create([
        'name' => 'Rudi',
        'rating' => 4.9,
        'service_fee' => 50000,
        'is_active' => true,
        'description' => 'Specialist fade.',
    ]);

    $this->actingAs($admin)
        ->get('/admin/capsters/edit')
        ->assertRedirect(route('admin.capsters.edit', $capster));
});
