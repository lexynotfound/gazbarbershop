<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.settings.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $user = $request->user();

        unset($attributes['current_password'], $attributes['password_confirmation']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $attributes['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (blank($attributes['password'] ?? null)) {
            unset($attributes['password']);
        }

        $user->update($attributes);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Pengaturan akun admin berhasil diperbarui.');
    }
}
