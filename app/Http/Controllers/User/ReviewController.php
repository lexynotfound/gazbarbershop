<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Collection<int, Booking> $reviewableBookings */
        $reviewableBookings = Booking::query()
            ->with(['capster', 'items.service'])
            ->whereBelongsTo($request->user())
            ->where('status', 'COMPLETED')
            ->doesntHave('review')
            ->latest('booking_start')
            ->get();

        $selectedBooking = $reviewableBookings->firstWhere('id', $request->integer('booking'));

        return view('user.bookings.review', [
            'reviewableBookings' => $reviewableBookings,
            'selectedBooking' => $selectedBooking,
        ]);
    }

    public function store(StoreReviewRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $booking = Booking::query()
                ->whereBelongsTo(Auth::user())
                ->where('status', 'COMPLETED')
                ->doesntHave('review')
                ->lockForUpdate()
                ->findOrFail($validated['booking_id']);

            Review::query()->create([
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'capster_id' => $booking->capster_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);

            $booking->update(['status' => 'REVIEWED']);

            $averageRating = Review::query()
                ->where('capster_id', $booking->capster_id)
                ->avg('rating');

            $booking->capster()->update([
                'rating' => round((float) $averageRating, 1),
            ]);
        });

        return redirect()
            ->route('booking.review')
            ->with('status', 'Ulasan berhasil dikirim. Terima kasih atas penilaiannya.');
    }
}
