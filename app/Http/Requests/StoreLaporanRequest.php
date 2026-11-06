<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori' => ['required', 'in:kebersihan,keamanan,fasilitas,lainnya'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'kategori.required' => 'Kategori laporan wajib dipilih.',
            'kategori.in' => 'Kategori laporan tidak valid.',
            'judul.required' => 'Judul laporan wajib diisi.',
            'deskripsi.required' => 'Deskripsi laporan wajib diisi.',
            'foto.image' => 'Foto harus berupa file gambar.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
