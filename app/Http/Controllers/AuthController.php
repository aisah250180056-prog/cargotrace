<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Halaman form login web
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses Login (Mendukung Web Session & Sanctum REST API - Slide 12)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Cek autentikasi
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Email atau password salah.'], 401);
            }

            throw ValidationException::withMessages([
                'email' => 'Email atau password yang Anda masukkan tidak cocok.',
            ]);
        }

        $user = Auth::user();

        // Jika request dari REST API / Postman (menggunakan Bearer Token)
        if ($request->wantsJson()) {
            $token = $user->createToken('cargotrace_api_token')->plainTextToken;
            return response()->json([
                'message' => 'Login berhasil!',
                'token'   => $token,
                'user'    => $user,
            ]);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('shipments.index'))->with('success', "Selamat datang kembali, {$user->name}!");
    }

    /**
     * Proses Logout
     */
    public function logout(Request $request)
    {
        if ($request->user() && $request->wantsJson()) {
            // Hapus token API aktif
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logout berhasil. Token telah dicabut.']);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Registrasi Akun Customer Baru (Slide 12: POST /api/register)
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'customer', // Default role pendaftaran adalah customer
        ]);

        if ($request->wantsJson()) {
            $token = $user->createToken('cargotrace_api_token')->plainTextToken;
            return response()->json([
                'message' => 'Registrasi berhasil!',
                'token'   => $token,
                'user'    => $user,
            ], 201);
        }

        Auth::login($user);
        return redirect()->route('shipments.index')->with('success', 'Akun berhasil dibuat!');
    }
}