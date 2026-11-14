<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Menampilkan profil akun yang sedang login.
     */
    public function show(Request $request): View
    {
        /** @var Pengguna $user */
        $user = $request->user();

        $user->load([
            'instansi',
            'rumahSakit',
        ]);

        $roleLabels = [
            'pelapor' => 'Pelapor',
            'petugas_ec' => 'Petugas EC',
            'instansi' => 'Instansi',
            'rumah_sakit' => 'Rumah Sakit',
        ];

        // Halaman default berdasarkan aktor.
        $defaultBackRoute = match ($user->role) {
            'pelapor' => 'pelapor.laporan.index',
            'petugas_ec' => 'petugas.ec.dashboard',
            'instansi' => 'instansi.assignments.index',
            'rumah_sakit' => 'hospital.referrals.index',
            default => null,
        };

        $backUrl = $defaultBackRoute
            && \Illuminate\Support\Facades\Route::has($defaultBackRoute)
                ? route($defaultBackRoute)
                : url('/');

        // Simpan halaman asal hanya jika dari aplikasi yang sama
        // dan bukan halaman Profil itu sendiri.
        $previousUrl = url()->previous();
        $previousPath = parse_url($previousUrl, PHP_URL_PATH);
        $appUrl = rtrim(url('/'), '/');

        if (
            is_string($previousUrl)
            && str_starts_with($previousUrl, $appUrl . '/')
            && !in_array($previousPath, [
                '/profile',
                '/profile/password',
            ], true)
        ) {
            $request->session()->put(
                'profile.back_url',
                $previousUrl
            );
        }

        // Gunakan URL asal yang tersimpan jika valid.
        $savedBackUrl = $request->session()->get(
            'profile.back_url'
        );

        if (
            is_string($savedBackUrl)
            && str_starts_with($savedBackUrl, $appUrl . '/')
            && !in_array(
                parse_url($savedBackUrl, PHP_URL_PATH),
                ['/profile', '/profile/password'],
                true
            )
        ) {
            $backUrl = $savedBackUrl;
        }

        return view('profile.show', [
            'user' => $user,
            'roleLabel' => $roleLabels[$user->role] ?? $user->role,
            'canEdit' => $user->role === 'pelapor',
            'backUrl' => $backUrl,
        ]);
    }

    /**
     * Memperbarui informasi profil khusus Pelapor.
     */
    public function update(
        Request $request
    ): RedirectResponse {
        /** @var Pengguna $user */
        $user = $request->user();

        abort_unless($user->role === 'pelapor', 403);

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('pengguna', 'email')
                    ->ignore($user->pengguna_id, 'pengguna_id'),
            ],
            'no_telp' => [
                'required',
                'string',
                'max:20',
            ],
        ]);

        $user->update($validated);

        return redirect()
            ->route('profile.show')
            ->with(
                'success',
                'Informasi profil berhasil diperbarui.'
            );
    }

    /**
     * Mengubah password khusus Pelapor.
     */
    public function updatePassword(
        Request $request
    ): RedirectResponse {
        /** @var Pengguna $user */
        $user = $request->user();

        abort_unless($user->role === 'pelapor', 403);

        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password:web',
            ],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $user->update([
            'password' => Hash::make(
                $validated['password']
            ),
        ]);

        return redirect()
            ->route('profile.show')
            ->with(
                'success',
                'Password berhasil diperbarui.'
            );
    }
}
