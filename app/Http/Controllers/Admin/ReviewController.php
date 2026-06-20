<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $reviews = Review::query()
            ->with(['booking', 'capster', 'user'])
            ->latest()
            ->get();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'averageRating' => (float) $reviews->avg('rating'),
        ]);
    }

    public function show(Review $review): View
    {
        $review->load(['booking.items.service', 'capster', 'user']);

        return view('admin.reviews.show', compact('review'));
    }
}
