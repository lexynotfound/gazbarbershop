<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Services\BookingAvailability;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RescheduleBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->booking()->status, Booking::RESCHEDULABLE_STATUSES, true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $booking = $this->booking();
                $durationMinutes = (int) $booking->items->sum('duration_minutes');

                $isAvailable = app(BookingAvailability::class)->isAvailable(
                    $booking->capster_id,
                    (string) $this->input('booking_date'),
                    (string) $this->input('booking_time'),
                    $durationMinutes,
                    $booking->id,
                );

                if (! $isAvailable) {
                    $validator->errors()->add('booking_time', 'Jadwal capster tidak tersedia atau sudah dibooking.');
                }
            },
        ];
    }

    private function booking(): Booking
    {
        return $this->route('booking');
    }
}
