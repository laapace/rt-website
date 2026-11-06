<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTarifRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'min:1'],
            'periode_mulai' => ['required', 'date'],
            'periode_selesai' => ['nullable', 'date', 'after_or_equal:periode_mulai'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama tarif wajib diisi.',
            'nominal.required' => 'Nominal tarif wajib diisi.',
            'nominal.min' => 'Nominal tarif minimal Rp1.',
            'periode_mulai.required' => 'Periode mulai wajib diisi.',
            'periode_mulai.date' => 'Periode mulai tidak valid.',
            'periode_selesai.date' => 'Periode selesai tidak valid.',
            'periode_selesai.after_or_equal' => 'Periode selesai tidak boleh sebelum periode mulai.',
        ];
    }
}
