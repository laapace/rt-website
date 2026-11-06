<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Akun yang sudah punya riwayat tidak bisa dihapus: FK ke users di
        // surat, laporan, pembayaran, kas, pengumuman, agenda, dan ronda
        // memakai restrictOnDelete supaya riwayat RT tidak hilang. Tanpa
        // pengecekan ini, delete() meledak jadi 500 (FK constraint).
        // Peran dan notifikasi ikut terhapus otomatis (cascade), log
        // aktivitas dibuat NULL (nullOnDelete).
        $riwayatAda = DB::table('surat_pengajuan')->where('diajukan_oleh_user_id', $user->id)->exists()
            || DB::table('laporan_warga')->where('dilaporkan_oleh_user_id', $user->id)->exists()
            || DB::table('pembayaran')->where('dibayar_oleh_user_id', $user->id)->exists()
            || DB::table('kas')->where('dicatat_oleh', $user->id)->exists()
            || DB::table('pengumuman')->where('dibuat_oleh', $user->id)->exists()
            || DB::table('agenda')->where('dibuat_oleh', $user->id)->exists()
            || DB::table('jadwal_ronda')->where('dibuat_oleh', $user->id)->exists();

        if ($riwayatAda) {
            return Redirect::route('profile.edit')
                ->with('error', 'Akun tidak bisa dihapus karena masih memiliki riwayat (surat, laporan, pembayaran, atau lainnya). Hubungi Admin RT bila akun ini memang harus dihapus.');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
