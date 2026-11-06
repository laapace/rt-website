<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadBuktiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'nominal_dibayar' => ['required', 'numeric', 'min:1'],
            'metode' => ['required', 'in:transfer,tunai,qris'],
            'tanggal_bayar' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'bukti.required' => 'Bukti bayar wajib diunggah.',
            'bukti.mimes' => 'Bukti bayar harus berupa JPG, JPEG, PNG, atau PDF.',
            'bukti.max' => 'Ukuran bukti bayar maksimal 2 MB.',
            'nominal_dibayar.required' => 'Nominal yang dibayar wajib diisi.',
            'nominal_dibayar.min' => 'Nominal yang dibayar minimal Rp1.',
            'metode.required' => 'Metode pembayaran wajib dipilih.',
            'metode.in' => 'Metode pembayaran tidak valid.',
            'tanggal_bayar.required' => 'Tanggal bayar wajib diisi.',
            'tanggal_bayar.date' => 'Tanggal bayar tidak valid.',
        ];
    }
}
