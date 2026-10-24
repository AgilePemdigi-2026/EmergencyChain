<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmergencyCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'jenis_kejadian' => [
                'required',
                'string',
                Rule::in([
                    'Kecelakaan Lalu Lintas',
                    'Kebakaran',
                    'Bencana Alam',
                    'Darurat Medis',
                    'Lainnya',
                ]),
            ],
            'tingkat_kebutuhan' => [
                'required',
                Rule::in([
                    'Butuh Penanganan Cepat',
                    'Butuh Penanganan Segera',
                ]),
            ],
            'kondisi_kejadian' => [
                'required',
                'string',
                'max:500',
            ],
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
            'alamat' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Data laporan belum lengkap, silakan lengkapi terlebih dahulu.',
            'latitude.between' => 'Latitude tidak valid.',
            'longitude.between' => 'Longitude tidak valid.',
        ];
    }
}
