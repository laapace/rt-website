<?php

namespace App\Http\Requests;

use App\Models\Warga;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWargaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $warga = $this->route('anggota') ?? $this->route('warga');
        $wargaId = $warga instanceof Warga ? $warga->id : $warga;

        return [
            'kartu_keluarga_id' => ['sometimes', 'exists:kartu_keluarga,id'],
            'nik' => ['required', 'digits:16', Rule::unique('warga', 'nik')->ignore($wargaId)],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'hubungan_keluarga' => ['required', 'string', 'max:100'],
            'pekerjaan' => ['nullable', 'string', 'max:255'],
            'status_tinggal' => ['required', 'in:aktif,pindah,meninggal'],
        ];
    }

    public function messages(): array
    {
        return [
            'kartu_keluarga_id.exists' => 'Kartu keluarga tidak ditemukan.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar untuk warga lain.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P.',
            'hubungan_keluarga.required' => 'Hubungan keluarga wajib diisi.',
            'status_tinggal.required' => 'Status tinggal wajib dipilih.',
            'status_tinggal.in' => 'Status tinggal tidak valid.',
        ];
    }
}
