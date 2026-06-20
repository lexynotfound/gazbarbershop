<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function fakeAvatarPngUpload(string $name): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'avatar-photo-');

    file_put_contents(
        $path,
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
    );

    return new UploadedFile($path, $name, 'image/png', null, true);
}

test('user can open profile page with their current data', function () {
    $user = User::factory()->create([
        'name' => 'Member Demo',
        'email' => 'member@example.com',
        'phone' => '6281234567002',
        'role' => 'user',
    ]);

    $this->actingAs($user)
        ->get(route('profile'))
        ->assertSuccessful()
        ->assertSee('Profil')
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee(route('profile.update'), false)
        ->assertSee('Member Demo')
        ->assertSee('member@example.com')
        ->assertSee('6281234567002')
        ->assertSee('Ubah Password');
});

test('user can update profile bio data', function () {
    $user = User::factory()->create([
        'name' => 'Member Lama',
        'email' => 'lama@example.com',
        'phone' => '628111111111',
        'role' => 'user',
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Member Baru',
            'email' => 'baru@example.com',
            'phone' => '628222222222',
        ])
        ->assertRedirect(route('profile'))
        ->assertSessionHas('status', 'Profil berhasil diperbarui.');

    $user->refresh();

    expect($user->name)->toBe('Member Baru')
        ->and($user->email)->toBe('baru@example.com')
        ->and($user->phone)->toBe('628222222222');
});

test('user can upload avatar and replace the old avatar', function () {
    Storage::fake('public');

    $oldAvatar = 'avatars/old.png';
    Storage::disk('public')->put($oldAvatar, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));

    $user = User::factory()->create([
        'name' => 'Member Avatar',
        'email' => 'avatar@example.com',
        'phone' => '628333333333',
        'avatar' => $oldAvatar,
        'role' => 'user',
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Member Avatar',
            'email' => 'avatar@example.com',
            'phone' => '628333333333',
            'avatar' => fakeAvatarPngUpload('new-avatar.png'),
        ])
        ->assertRedirect(route('profile'));

    $user->refresh();

    expect($user->avatar)->not->toBeNull()
        ->and($user->avatar)->not->toBe($oldAvatar);

    Storage::disk('public')->assertMissing($oldAvatar);
    Storage::disk('public')->assertExists($user->avatar);
});

test('user can keep their own email but cannot use another users email', function () {
    $user = User::factory()->create([
        'email' => 'member@example.com',
        'role' => 'user',
    ]);
    $otherUser = User::factory()->create([
        'email' => 'taken@example.com',
        'role' => 'user',
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Member Demo',
            'email' => 'member@example.com',
            'phone' => null,
        ])
        ->assertSessionHasNoErrors();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Member Demo',
            'email' => $otherUser->email,
            'phone' => null,
        ])
        ->assertSessionHasErrors('email');
});

test('user can update their password with current password', function () {
    $user = User::factory()->create([
        'email' => 'password@example.com',
        'password' => Hash::make('old-password'),
        'role' => 'user',
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect(route('profile'))
        ->assertSessionHas('status', 'Profil berhasil diperbarui.');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('current password is required and must be valid when changing password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
        'role' => 'user',
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('old-password', $user->refresh()->password))->toBeTrue();
});

test('new password must be confirmed', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
        'role' => 'user',
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ])
        ->assertSessionHasErrors('password');

    expect(Hash::check('old-password', $user->refresh()->password))->toBeTrue();
});

test('profile update is restricted to authenticated regular users', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->get(route('profile'))->assertRedirect(route('login'));

    $this->actingAs($admin)
        ->get(route('profile'))
        ->assertRedirect(route('admin.dashboard'));
});
