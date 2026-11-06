<?php

namespace App\Http\Requests;

use App\Models\JadwalRonda;
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
            // (tanggal, blok) unik di database — tanpa aturan ini, jadwal
            // ganda meledak jadi 500 (UniqueConstraintViolationException).
            // whereDate dipakai (bukan Rule::unique mentah) karena nilai
            // tanggal tersimpan sebagai datetime di SQLite.
            'tanggal' => ['required', 'date', function ($attribute, $value, $fail) {
                $duplikat = JadwalRonda::whereDate('tanggal', $value)
                    ->where('blok', $this->input('blok'))
                    ->exists();

                if ($duplikat) {
                    $fail('Sudah ada jadwal ronda untuk tanggal dan blok tersebut.');
                }
            }],
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
            'tanggal.unique' => 'Sudah ada jadwal ronda untuk tanggal dan blok tersebut.',
            'blok.required' => 'Blok wajib diisi.',
            'petugas.array' => 'Daftar petugas tidak valid.',
            'petugas.*.exists' => 'Salah satu petugas tidak ditemukan.',
        ];
    }
}
