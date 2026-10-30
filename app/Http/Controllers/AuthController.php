<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'no_telp' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:pengguna,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'agreement' => ['accepted'],
        ]);

        $pengguna = Pengguna::create([
            'nama' => $validated['nama'],
            'no_telp' => $validated['no_telp'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($pengguna);
        $request->session()->regenerate();

        return redirect()
            ->route('pelapor.laporan.create')
            ->with('success', 'Registrasi berhasil!');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();
        return match (Auth::user()->role) {
            'pelapor' => redirect()
                ->route('pelapor.laporan.create'),

            'petugas_ec' => redirect()
                ->route('petugas.ec.cases.index'),

            'instansi' => redirect()
                ->route('instansi.assignments.index'),

            default => abort(
                403,
                'Halaman untuk role akun ini belum tersedia.'
            ),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
