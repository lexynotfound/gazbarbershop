<?php

namespace App\Http\Requests;

use App\Models\CapsterSchedule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveCapsterScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'capster_id' => ['required', 'integer', Rule::exists('capsters', 'id')],
            'work_date' => [
                'required',
                Rule::date()->format('Y-m-d'),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'is_available' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $validator->errors()->hasAny(['capster_id', 'work_date'])) {
                    $schedule = $this->route('schedule');
                    $duplicateExists = CapsterSchedule::query()
                        ->where('capster_id', $this->integer('capster_id'))
                        ->whereDate('work_date', $this->string('work_date')->toString())
                        ->when(
                            $schedule instanceof CapsterSchedule,
                            fn ($query) => $query->whereKeyNot($schedule->id),
                        )
                        ->exists();

                    if ($duplicateExists) {
                        $validator->errors()->add('work_date', 'Capster ini sudah memiliki jadwal pada tanggal tersebut.');
                    }
                }

                if ($validator->errors()->hasAny(['start_time', 'end_time'])) {
                    return;
                }

                $startTime = $this->string('start_time')->toString();
                $endTime = $this->string('end_time')->toString();

                if ($startTime < CapsterSchedule::OPERATING_START || $startTime >= CapsterSchedule::OPERATING_END) {
                    $validator->errors()->add('start_time', 'Jam mulai harus berada antara 10:00 dan sebelum 22:00.');
                }

                if ($endTime > CapsterSchedule::OPERATING_END) {
                    $validator->errors()->add('end_time', 'Jam selesai tidak boleh melewati 22:00.');
                }

                if ($endTime <= $startTime) {
                    $validator->errors()->add('end_time', 'Jam selesai harus setelah jam mulai.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'capster_id' => 'capster',
            'work_date' => 'tanggal kerja',
            'start_time' => 'jam mulai',
            'end_time' => 'jam selesai',
            'is_available' => 'status ketersediaan',
        ];
    }
}
