<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmergencyCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'petugas_ec';
    }

    public function rules(): array
    {
        return [
            'kondisi' => [
                'required',
                'string',
                'max:500',
            ],
            'kebutuhan' => [
                'required',
                'string',
                Rule::in([
                    'Butuh Penanganan Cepat',
                    'Butuh Penanganan Segera',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'kondisi.required' =>
                'Data Emergency Case gagal disimpan. Kondisi kejadian wajib diisi.',

            'kebutuhan.required' =>
                'Data Emergency Case gagal disimpan. Kebutuhan penanganan wajib dipilih.',

            'kebutuhan.in' =>
                'Data Emergency Case gagal disimpan. Pilihan kebutuhan tidak valid.',

            'kondisi.max' =>
                'Kondisi kejadian maksimal 500 karakter.',
        ];
    }
}
