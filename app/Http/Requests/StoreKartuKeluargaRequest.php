<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKartuKeluargaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_kk' => ['required', 'digits:16', 'unique:kartu_keluarga,no_kk'],
            'alamat' => ['required', 'string'],
            'blok' => ['required', 'string', 'max:100'],
            'rt' => ['required', 'string', 'max:10'],
            'rw' => ['required', 'string', 'max:10'],
            'status_tinggal' => ['required', 'in:aktif,pindah,meninggal'],
            // Kepala keluarga (langkah 2 saat tambah KK) — opsional.
            'nama_kepala' => ['nullable', 'string', 'max:255'],
            'nik_kepala' => ['nullable', 'digits:16', 'unique:warga,nik'],
            'jenis_kelamin_kepala' => ['nullable', 'in:L,P'],
            'tanggal_lahir_kepala' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_kk.required' => 'Nomor KK wajib diisi.',
            'no_kk.digits' => 'Nomor KK harus 16 digit.',
            'no_kk.unique' => 'Nomor KK sudah terdaftar.',
            'alamat.required' => 'Alamat wajib diisi.',
            'blok.required' => 'Blok wajib diisi.',
            'rt.required' => 'RT wajib diisi.',
            'rw.required' => 'RW wajib diisi.',
            'status_tinggal.required' => 'Status tinggal wajib dipilih.',
            'status_tinggal.in' => 'Status tinggal tidak valid.',
            'nik_kepala.digits' => 'NIK kepala keluarga harus 16 digit.',
            'nik_kepala.unique' => 'NIK kepala keluarga sudah terdaftar.',
            'jenis_kelamin_kepala.in' => 'Jenis kelamin kepala keluarga harus L atau P.',
            'tanggal_lahir_kepala.date' => 'Tanggal lahir kepala keluarga tidak valid.',
        ];
    }
}
