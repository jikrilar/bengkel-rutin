<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:120'],
            'year' => ['required', 'integer', 'min:1900', 'max:'.now()->year],
            'plate_number' => ['required', 'string', 'max:30'],
            'current_odometer' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'knows_last_service' => ['required', 'boolean'],
            'last_service_date' => ['nullable', 'required_if:knows_last_service,1', 'date', 'before_or_equal:today'],
            'last_service_odometer' => [
                'nullable',
                'required_if:knows_last_service,1',
                'integer',
                'min:0',
                'lte:current_odometer',
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'year.max' => 'Tahun kendaraan tidak boleh melebihi tahun berjalan.',
            'last_service_date.required_if' => 'Tanggal servis terakhir wajib diisi.',
            'last_service_date.before_or_equal' => 'Tanggal servis terakhir tidak boleh di masa depan.',
            'last_service_odometer.required_if' => 'Odometer saat servis terakhir wajib diisi.',
            'last_service_odometer.lte' => 'Odometer servis terakhir tidak boleh melebihi odometer saat ini.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'Nama kendaraan',
            'brand' => 'Merek',
            'model' => 'Model',
            'year' => 'Tahun',
            'plate_number' => 'Nomor polisi',
            'current_odometer' => 'Odometer saat ini',
        ];
    }
}
