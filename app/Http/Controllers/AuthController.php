<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Kalau sudah login, langsung ke dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Ambil data dari form login
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:SO,CBM,REVIEWER',
        ]);

        // Cek email + password + role
        if (Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
        ])) {

            // Buat session baru setelah berhasil login
            $request->session()->regenerate();

            // LANGSUNG KE DASHBOARD
            return redirect()->route('dashboard');
        }

        // Kalau salah, kembali ke login
        return back()
            ->withInput($request->only('email', 'role'))
            ->withErrors([
                'email' => 'Email, password, atau role tidak sesuai.',
            ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}