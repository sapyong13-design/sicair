<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\CutiTahunanCalculator;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cutiInfo = null;

        if ($user->masa_kerja_mulai && $user->sudahBekerjaSatuTahun()) {
            $calculator = new CutiTahunanCalculator($user);
            $cutiInfo = $calculator->hitung();
        }

        return view('profile.index', compact('user', 'cutiInfo'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:500',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * #48: Save notification preferences
     */
    public function updateNotificationPreferences(Request $request)
    {
        $keys = ['email_on_approve', 'email_on_reject', 'email_on_pending', 'email_on_decision'];
        $prefs = [];
        foreach ($keys as $key) {
            $prefs[$key] = $request->boolean($key);
        }

        Auth::user()->update(['notification_preferences' => $prefs]);

        return back()->with('success', 'Preferensi notifikasi berhasil disimpan.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
