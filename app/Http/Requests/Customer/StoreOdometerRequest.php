<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreOdometerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'odometer' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'recorded_at' => ['required', 'date', 'before_or_equal:now'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'odometer.required' => 'Odometer baru wajib diisi.',
            'recorded_at.required' => 'Waktu pencatatan wajib diisi.',
            'recorded_at.before_or_equal' => 'Waktu pencatatan tidak boleh di masa depan.',
        ];
    }
}
