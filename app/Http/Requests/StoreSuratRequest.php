<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', 'in:domisili,tidak_mampu,usaha,kelahiran,kematian,lainnya'],
            'keperluan' => ['required', 'string'],
            'data_surat' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis.required' => 'Jenis surat wajib dipilih.',
            'jenis.in' => 'Jenis surat tidak valid.',
            'keperluan.required' => 'Keperluan wajib diisi.',
            'data_surat.array' => 'Data tambahan surat tidak valid.',
        ];
    }
}
