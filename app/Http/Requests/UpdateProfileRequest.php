<?php

namespace App\Http\Requests;

use App\Services\PhoneNumberFormatter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()?->id),
            ],
            'phone' => ['nullable', 'string', 'max:16', 'regex:/^628[0-9]{8,12}$/'],
            'avatar' => ['nullable', File::image()->max(2 * 1024)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => PhoneNumberFormatter::toIndonesianMobile($this->string('phone')->toString()),
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor WhatsApp harus memakai nomor HP Indonesia yang valid, contoh 08123456789.',
        ];
    }
}
