<?php

namespace App\Http\Controllers;

use App\Models\Capster;
use App\Models\Service;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'services' => $this->activeServices(limit: 4),
            'capsters' => Capster::query()
                ->where('is_active', true)
                ->orderByDesc('rating')
                ->limit(3)
                ->get()
                ->map(fn (Capster $capster): array => [
                    'name' => $capster->name,
                    'photo' => $capster->photo,
                    'rating' => $capster->rating,
                    'service_fee' => $capster->service_fee,
                ])
                ->values(),
        ]);
    }

    public function services(): View
    {
        return view('pages.services', [
            'services' => $this->activeServices(),
        ]);
    }

    public function capsters(): View
    {
        return view('pages.capsters', [
            'capsters' => Capster::query()
                ->where('is_active', true)
                ->orderByDesc('rating')
                ->get()
                ->map(fn (Capster $capster): array => [
                    'name' => $capster->name,
                    'photo' => $capster->photo,
                    'rating' => $capster->rating,
                    'service_fee' => $capster->service_fee,
                ])
                ->values(),
        ]);
    }

    /**
     * @return Collection<int, array{id: int, name: string, description: ?string, image: ?string, price: int, duration: int}>
     */
    private function activeServices(?int $limit = null): Collection
    {
        return Service::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->when($limit, fn ($query) => $query->limit($limit))
            ->get()
            ->map(fn (Service $service): array => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'image' => $service->image,
                'price' => $service->price,
                'duration' => $service->duration_minutes,
            ])
            ->values();
    }
}
