<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReviewRequest extends FormRequest
{
    private ?Booking $booking = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $booking = $this->reviewBooking();

        if (! $booking) {
            return true;
        }

        return $booking->user_id === $this->user()?->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->has('booking_id')) {
                return;
            }

            $booking = $this->reviewBooking();

            if (! $booking) {
                return;
            }

            if ($booking->status !== 'COMPLETED') {
                $validator->errors()->add('booking_id', 'Booking hanya bisa direview setelah selesai cukur.');
            }

            if ($booking->review()->exists()) {
                $validator->errors()->add('booking_id', 'Booking ini sudah pernah direview.');
            }
        });
    }

    private function reviewBooking(): ?Booking
    {
        if ($this->booking !== null) {
            return $this->booking;
        }

        $bookingId = $this->integer('booking_id');

        if ($bookingId === 0) {
            return null;
        }

        $this->booking = Booking::query()->find($bookingId);

        return $this->booking;
    }
}
