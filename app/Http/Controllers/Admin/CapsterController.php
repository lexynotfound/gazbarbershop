<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCapsterRequest;
use App\Http\Requests\UpdateCapsterRequest;
use App\Models\Capster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CapsterController extends Controller
{
    public function index(): View
    {
        $capsters = Capster::query()
            ->latest()
            ->get();

        return view('admin.capsters.index', compact('capsters'));
    }

    public function create(): View
    {
        return view('admin.capsters.create');
    }

    public function store(StoreCapsterRequest $request): RedirectResponse
    {
        $attributes = $request->validated();

        if ($request->hasFile('photo')) {
            $attributes['photo'] = $request->file('photo')->store('capsters', 'public');
        }

        Capster::query()->create($attributes);

        return redirect()
            ->route('admin.capsters.index')
            ->with('status', 'Capster berhasil ditambahkan.');
    }

    public function editFirst(): RedirectResponse
    {
        $capster = Capster::query()
            ->oldest()
            ->first();

        if (! $capster) {
            return redirect()->route('admin.capsters.create');
        }

        return redirect()->route('admin.capsters.edit', $capster);
    }

    public function edit(Capster $capster): View
    {
        return view('admin.capsters.edit', compact('capster'));
    }

    public function update(UpdateCapsterRequest $request, Capster $capster): RedirectResponse
    {
        $attributes = $request->validated();

        if ($request->hasFile('photo')) {
            if ($capster->photo) {
                Storage::disk('public')->delete($capster->photo);
            }

            $attributes['photo'] = $request->file('photo')->store('capsters', 'public');
        }

        $capster->update($attributes);

        return redirect()
            ->route('admin.capsters.index')
            ->with('status', 'Capster berhasil diperbarui.');
    }

    public function destroy(Capster $capster): RedirectResponse
    {
        if ($capster->photo) {
            Storage::disk('public')->delete($capster->photo);
        }

        $capster->delete();

        return redirect()
            ->route('admin.capsters.index')
            ->with('status', 'Capster berhasil dihapus.');
    }
}
