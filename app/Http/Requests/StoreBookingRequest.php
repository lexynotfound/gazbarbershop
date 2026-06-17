<?php

namespace App\Http\Requests;

use App\Models\Service;
use App\Services\BookingAvailability;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'distinct', Rule::exists('services', 'id')->where('is_active', true)],
            'capster_id' => ['required', 'integer', Rule::exists('capsters', 'id')->where('is_active', true)],
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

                $durationMinutes = (int) Service::query()
                    ->whereIn('id', $this->input('service_ids', []))
                    ->where('is_active', true)
                    ->sum('duration_minutes');

                $isAvailable = app(BookingAvailability::class)->isAvailable(
                    (int) $this->input('capster_id'),
                    (string) $this->input('booking_date'),
                    (string) $this->input('booking_time'),
                    $durationMinutes,
                );

                if (! $isAvailable) {
                    $validator->errors()->add('booking_time', 'Jadwal capster tidak tersedia atau sudah dibooking.');
                }
            },
        ];
    }
}
