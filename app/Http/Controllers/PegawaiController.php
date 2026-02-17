<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status_pegawai')) {
            $query->where('status_pegawai', $request->status_pegawai);
        }

        $pegawai = $query->paginate(15)->withQueryString();

        return view('pegawai.index', compact('pegawai'));
    }

    public function create()
    {
        $atasanOptions = User::whereIn('role', ['atasan', 'panitera', 'sekretaris', 'ketua'])
            ->orderBy('name')
            ->get();

        return view('pegawai.form', [
            'pegawai' => null,
            'atasanOptions' => $atasanOptions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|size:18|unique:users,nip',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,ketua,atasan,panitera,sekretaris,pegawai,hakim,hakim_ad_hoc',
            'jabatan' => 'nullable|string|max:255',
            'golongan_ruang' => 'nullable|string|max:10',
            'unit_kerja' => 'required|string|max:255',
            'masa_kerja_mulai' => 'nullable|date',
            'status_pegawai' => 'required|in:hakim,aparatur,cpns,cakim',
            'jenis_kelamin' => 'required|in:L,P',
            'jumlah_anak' => 'nullable|integer|min:0',
            'lokasi_terpencil' => 'nullable|boolean',
            'atasan_id' => 'nullable|exists:users,id',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'leave_balance' => 'nullable|integer|min:0',
        ]);

        $validated['lokasi_terpencil'] = $request->boolean('lokasi_terpencil');
        $validated['leave_balance'] = $validated['leave_balance'] ?? 12;

        User::create($validated);

        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function show(User $pegawai)
    {
        $pegawai->load(['atasan', 'leaveRequests' => function ($q) {
            $q->latest()->take(10);
        }, 'cutiRecords' => function ($q) {
            $q->orderByDesc('tahun');
        }]);

        return view('pegawai.show', compact('pegawai'));
    }

    public function edit(User $pegawai)
    {
        $atasanOptions = User::whereIn('role', ['atasan', 'panitera', 'sekretaris', 'ketua'])
            ->where('id', '!=', $pegawai->id)
            ->orderBy('name')
            ->get();

        return view('pegawai.form', [
            'pegawai' => $pegawai,
            'atasanOptions' => $atasanOptions,
        ]);
    }

    public function update(Request $request, User $pegawai)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|size:18|unique:users,nip,' . $pegawai->id,
            'role' => 'required|in:admin,ketua,atasan,panitera,sekretaris,pegawai,hakim,hakim_ad_hoc',
            'jabatan' => 'nullable|string|max:255',
            'golongan_ruang' => 'nullable|string|max:10',
            'unit_kerja' => 'required|string|max:255',
            'masa_kerja_mulai' => 'nullable|date',
            'status_pegawai' => 'required|in:hakim,aparatur,cpns,cakim',
            'jenis_kelamin' => 'required|in:L,P',
            'jumlah_anak' => 'nullable|integer|min:0',
            'lokasi_terpencil' => 'nullable|boolean',
            'atasan_id' => 'nullable|exists:users,id',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'leave_balance' => 'nullable|integer|min:0',
        ]);

        $validated['lokasi_terpencil'] = $request->boolean('lokasi_terpencil');

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $validated['password'] = $request->password;
        }

        $pegawai->update($validated);

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(User $pegawai)
    {
        $pegawai->delete();

        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}
