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
    $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'role' => 'required|in:SO,CBM,REVIEWER',
    ]);

    if (Auth::attempt([
        'email' => $data['email'],
        'password' => $data['password'],
    ])) {

        $request->session()->regenerate();

        // Simpan role yang dipilih saat login
        session(['login_role' => $data['role']]);

        return redirect()->route('dashboard');
    }

    return back()
        ->withInput($request->only('email', 'role'))
        ->withErrors([
            'email' => 'Email atau password tidak sesuai.',
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