<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return view('search.results', [
                'query' => $q,
                'pegawai' => collect(),
                'leaves' => collect(),
            ]);
        }

        $pegawai = User::where('name', 'like', "%{$q}%")
            ->orWhere('nip', 'like', "%{$q}%")
            ->orWhere('unit_kerja', 'like', "%{$q}%")
            ->orWhere('jabatan', 'like', "%{$q}%")
            ->limit(10)
            ->get();

        $leaves = LeaveRequest::with('user')
            ->where(function ($query) use ($q) {
                $query->whereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"))
                    ->orWhere('type', 'like', "%{$q}%")
                    ->orWhere('reason', 'like', "%{$q}%");
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('search.results', compact('pegawai', 'leaves', 'q'));
    }
}
