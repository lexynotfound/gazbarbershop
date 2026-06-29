<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('admin settings sidebar links to a dedicated settings page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee(route('admin.settings.edit'), false);

    $this->actingAs($admin)
        ->get(route('admin.settings.edit'))
        ->assertSuccessful()
        ->assertSee('Pengaturan')
        ->assertSee('Akun Administrator')
        ->assertSee(route('admin.settings.update'), false);
});

test('admin can update account settings', function () {
    $admin = User::factory()->create([
        'name' => 'Admin Lama',
        'email' => 'admin-lama@example.com',
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.settings.update'), [
            'name' => 'Admin GAZ',
            'email' => 'admin@gaz.test',
            'phone' => '081234567890',
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHas('status', 'Pengaturan akun admin berhasil diperbarui.');

    $admin->refresh();

    expect($admin->name)->toBe('Admin GAZ')
        ->and($admin->email)->toBe('admin@gaz.test')
        ->and($admin->phone)->toBe('6281234567890');
});

test('admin can update password from settings', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'password' => Hash::make('password-lama'),
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.settings.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'phone' => '',
            'current_password' => 'password-lama',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])
        ->assertRedirect(route('admin.settings.edit'));

    expect(Hash::check('password-baru', $admin->fresh()->password))->toBeTrue();
});

test('regular users cannot access admin settings', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)
        ->get(route('admin.settings.edit'))
        ->assertForbidden();

    $this->actingAs($user)
        ->patch(route('admin.settings.update'), [
            'name' => 'Unauthorized',
            'email' => 'unauthorized@example.com',
        ])
        ->assertForbidden();
});
