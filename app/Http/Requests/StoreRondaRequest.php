<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRondaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'blok' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'petugas' => ['nullable', 'array'],
            'petugas.*' => ['exists:warga,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal ronda wajib diisi.',
            'tanggal.date' => 'Tanggal ronda tidak valid.',
            'blok.required' => 'Blok wajib diisi.',
            'petugas.array' => 'Daftar petugas tidak valid.',
            'petugas.*.exists' => 'Salah satu petugas tidak ditemukan.',
        ];
    }
}
