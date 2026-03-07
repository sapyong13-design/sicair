<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nip' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            AuditLog::create([
                'user_id'    => $user->id,
                'model'      => 'Auth',
                'model_id'   => $user->id,
                'action'     => 'login',
                'description' => "Login berhasil: {$user->name} ({$user->nip})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $user->update(['last_login_at' => now()]);

            return redirect()->intended('/dashboard');
        }

        AuditLog::create([
            'user_id'    => null,
            'action'     => 'login_failed',
            'model'      => 'Auth',
            'model_id'   => null,
            'old_values' => null,
            'new_values' => json_encode([
                'username_attempted' => $request->input('nip', $request->input('email', $request->input('username', '-')))
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->withErrors([
            'nip' => 'NIP atau password salah.',
        ])->onlyInput('nip');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'user_id'    => $user->id,
                'model'      => 'Auth',
                'model_id'   => $user->id,
                'action'     => 'logout',
                'description' => "Logout: {$user->name} ({$user->nip})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
