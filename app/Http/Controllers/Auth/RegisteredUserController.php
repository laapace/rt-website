<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\KartuKeluarga;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * Akun warga hanya dibuat kalau NIK + No. KK cocok dengan data warga
     * yang sudah diinput Sekretaris. Akun baru berstatus is_active=false
     * (menunggu aktivasi) dan TIDAK langsung login.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'no_kk' => ['required', 'digits:16'],
            'nik' => ['required', 'digits:16'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ], [
            'no_kk.required' => 'Nomor KK wajib diisi.',
            'no_kk.digits' => 'Nomor KK harus 16 digit.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit.',
        ]);

        $kartuKeluarga = KartuKeluarga::where('no_kk', $request->string('no_kk'))->first();
        $warga = Warga::where('nik', $request->string('nik'))->first();

        if (! $kartuKeluarga || ! $warga || $warga->kartu_keluarga_id !== $kartuKeluarga->id) {
            throw ValidationException::withMessages([
                'no_kk' => 'Data KK/NIK tidak ditemukan — hubungi Sekretaris RT',
                'nik' => 'Data KK/NIK tidak ditemukan — hubungi Sekretaris RT',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Penugasan langsung (bukan mass-assignment) agar tidak bergantung
        // pada daftar $fillable Model User.
        $user->no_hp = $request->string('no_hp')->toString() ?: null;
        $user->kartu_keluarga_id = $kartuKeluarga->id;
        $user->warga_id = $warga->id;
        $user->is_active = false;
        $user->save();

        $user->roles()->create(['peran' => 'warga']);

        event(new Registered($user));

        return redirect()->route('login')
            ->with('status', 'Pendaftaran berhasil. Akunmu menunggu aktivasi Sekretaris RT sebelum bisa login.');
    }
}
