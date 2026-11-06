<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKartuKeluargaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('warga')?->id ?? $this->route('warga');

        return [
            'no_kk' => ['required', 'digits:16', Rule::unique('kartu_keluarga', 'no_kk')->ignore($id)],
            'alamat' => ['required', 'string'],
            'blok' => ['required', 'string', 'max:100'],
            'rt' => ['required', 'string', 'max:10'],
            'rw' => ['required', 'string', 'max:10'],
            'status_tinggal' => ['required', 'in:aktif,pindah,meninggal'],
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
        ];
    }
}
