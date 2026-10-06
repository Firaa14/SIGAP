<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            'email' => 'required|email:rfc,dns',
            'password' => 'required',
            'role' => 'required|in:SO,CBM',
        ]);

        $emailDomain = Str::lower(Str::afterLast($data['email'], '@'));
        $blockedDomains = config('auth.login.blocked_email_domains', []);
        $allowedDomains = config('auth.login.allowed_email_domains', []);

        $isBlockedDomain = collect($blockedDomains)->contains(
            fn(string $domain): bool => $emailDomain === $domain || Str::endsWith($emailDomain, '.' . $domain)
        );
        $hasAllowedDomainList = $allowedDomains !== [];
        $isAllowedDomain = collect($allowedDomains)->contains(
            fn(string $domain): bool => $emailDomain === Str::lower($domain)
        );

        if ($isBlockedDomain || ($hasAllowedDomainList && !$isAllowedDomain)) {
            return back()
                ->withInput($request->only('email', 'role'))
                ->withErrors([
                    'email' => 'Gunakan alamat email dengan domain yang valid dan terdaftar.',
                ]);
        }

        if (
            Auth::attempt([
                'email' => $data['email'],
                'password' => $data['password'],
            ])
        ) {

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

        return redirect()->route('dashboard');
    }
}
