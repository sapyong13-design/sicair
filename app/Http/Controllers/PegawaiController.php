<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\AuditLog;
use App\Models\CutiRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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

        // Sprint 5 #32: Filter by active status
        if ($request->filled('active') && $request->active !== '') {
            if ($request->active === '1') {
                $query->where(fn($q) => $q->where('is_active', true)->orWhereNull('is_active'));
            } else {
                $query->where('is_active', false);
            }
        }

        $pegawai = $query->with(['atasan', 'cutiRecords'])->paginate(15)->withQueryString();

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

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $validated['lokasi_terpencil'] = $request->boolean('lokasi_terpencil');
        $validated['leave_balance'] = $validated['leave_balance'] ?? 12;
        // FIX #2: Explicitly hash password regardless of model cast
        $validated['password'] = Hash::make($validated['password']);

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

        $currentYear = (int) date('Y');
        $saldoYears = [];
        for ($i = 0; $i <= 2; $i++) {
            $yr = $currentYear - $i;
            $record = $pegawai->cutiRecords->firstWhere('tahun', $yr);
            $diambilAktual = $pegawai->leaveRequests()
                ->where('type', 'cuti_tahunan')
                ->whereIn('status', ['disetujui', 'approved'])
                ->whereYear('start_date', $yr)
                ->get()
                ->sum(fn($l) => $l->total_hari_kerja ?? $l->total_days ?? 0);
            $saldoYears[$yr] = [
                'year'               => $yr,
                'hak_cuti'           => $record->hak_cuti ?? 12,
                'carry_over'         => $record->carry_over ?? 0,
                'tambahan_terpencil' => $record->tambahan_terpencil ?? 0,
                'cuti_diambil'       => $record ? $record->cuti_diambil : (int) $diambilAktual,
                'sisa_cuti'          => $record->sisa_cuti ?? 0,
                'keterangan'         => $record->keterangan ?? '',
                'is_current'         => $yr === $currentYear,
            ];
        }

        return view('pegawai.show', compact('pegawai', 'saldoYears'));
    }

    public function updateSaldoCuti(Request $request, User $pegawai, int $year)
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $currentYear = (int) date('Y');
        abort_if($year < $currentYear - 2 || $year > $currentYear, 422, 'Tahun tidak valid.');

        $validated = $request->validate([
            'hak_cuti'           => 'required|integer|min:0|max:60',
            'carry_over'         => 'required|integer|min:0|max:24',
            'tambahan_terpencil' => 'required|integer|min:0|max:12',
            'sisa_cuti'          => 'required|integer|min:0|max:60',
            'keterangan'         => 'nullable|string|max:500',
        ]);

        $existing = CutiRecord::where('user_id', $pegawai->id)->where('tahun', $year)->first();
        $beforeData = $existing ? $existing->toArray() : null;

        $record = CutiRecord::updateOrCreate(
            ['user_id' => $pegawai->id, 'tahun' => $year],
            $validated
        );

        if ($year === $currentYear) {
            $pegawai->update(['leave_balance' => $validated['sisa_cuti']]);
        }

        AuditLog::log(
            'update_saldo_cuti',
            'CutiRecord',
            $record->id,
            $beforeData,
            $validated,
            "Admin memperbarui saldo cuti {$pegawai->name} tahun {$year}"
        );

        return back()->with('success', "Saldo cuti tahun {$year} untuk {$pegawai->name} berhasil diperbarui.");
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
            'email' => 'nullable|email|max:255|unique:users,email,' . $pegawai->id, // FIX #17
            'role' => 'required|in:admin,ketua,atasan,panitera,sekretaris,pegawai,hakim,hakim_ad_hoc',
            'jabatan' => 'nullable|string|max:255',
            'golongan_ruang' => 'nullable|string|max:10',
            'unit_kerja' => 'required|string|max:255',
            'masa_kerja_mulai' => 'nullable|date',
            'status_pegawai' => 'required|in:hakim,aparatur,cpns,cakim,pppk',
            'jenis_kelamin' => 'required|in:L,P',
            'jumlah_anak' => 'nullable|integer|min:0',
            'lokasi_terpencil' => 'nullable|boolean',
            'atasan_id' => ['nullable', 'exists:users,id', Rule::notIn([$pegawai->id])],
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'leave_balance' => 'nullable|integer|min:0',
            'photo' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
        ]);

        $validated['lokasi_terpencil'] = $request->boolean('lokasi_terpencil');

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            // FIX #2: Explicitly hash password to ensure it's never stored as plaintext
            $validated['password'] = Hash::make($request->password);
        }

        // #27 Photo upload
        if ($request->hasFile('photo')) {
            if ($pegawai->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pegawai->photo);
            }
            $validated['photo'] = $request->file('photo')->store('avatars', 'public');
        } else {
            unset($validated['photo']);
        }

        $pegawai->update($validated);

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(User $pegawai)
    {
        // Prevent deletion if pegawai has active/pending leave requests
        $activeLeavesCount = $pegawai->leaveRequests()
            ->whereIn('status', ['diajukan', 'pertimbangan_atasan'])
            ->count();

        if ($activeLeavesCount > 0) {
            return redirect()->route('pegawai.index')
                ->with('error', "Pegawai {$pegawai->name} memiliki {$activeLeavesCount} pengajuan cuti aktif. Selesaikan terlebih dahulu sebelum menghapus.");
        }

        // FIX #5/#11: Cegah penghapusan jika pegawai ini masih menjadi atasan langsung
        $bawahanCount = $pegawai->bawahan()->count();
        if ($bawahanCount > 0) {
            return redirect()->route('pegawai.index')
                ->with('error', "Pegawai {$pegawai->name} masih menjadi atasan langsung dari {$bawahanCount} pegawai lain. Pindahkan bawahan mereka ke atasan lain terlebih dahulu.");
        }

        $pegawai->delete();

        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }

    /**
     * #38 Quick View: Riwayat Cuti Pegawai (AJAX JSON)
     */
    public function riwayatCuti(User $pegawai)
    {
        $leaves = $pegawai->leaveRequests()
            ->whereYear('created_at', date('Y'))
            ->orWhereYear('created_at', date('Y') - 1)
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($l) => [
                'type_label' => $l->type_label,
                'start_date' => $l->start_date->format('d M Y'),
                'end_date'   => $l->end_date->format('d M Y'),
                'total_days' => $l->total_days,
                'status'     => $l->status,
                'status_label' => $l->status_label,
            ]);

        return response()->json(['leaves' => $leaves]);
    }

    /**
     * #36 Import Pegawai from Excel/CSV
     */
    public function importTemplate()
    {
        // Return a simple CSV template
        $headers = ['nama', 'nip', 'jabatan', 'golongan_ruang', 'unit_kerja', 'role', 'tanggal_mulai_kerja', 'status_pegawai', 'email'];
        $csv = implode(',', $headers) . "\n";
        $csv .= '"Contoh Nama","199001012010011001","Pranata Komputer","III/b","Kepaniteraan","pegawai","2010-01-01","aparatur","contoh@example.com"' . "\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="template-import-pegawai.csv"',
        ]);
    }

    /**
     * #36 Process Import
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('import_file');
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'csv') {
            $rows = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($rows);

            $imported = 0;
            $skipped = 0;
            foreach ($rows as $row) {
                if (count($row) < count($header)) continue;
                $data = array_combine($header, $row);
                if (empty($data['nip']) || empty($data['nama'])) { $skipped++; continue; }

                $exists = \App\Models\User::where('nip', $data['nip'])->exists();
                if ($exists) { $skipped++; continue; }

                \App\Models\User::create([
                    'name' => $data['nama'],
                    'nip'  => $data['nip'],
                    'jabatan' => $data['jabatan'] ?? null,
                    'golongan_ruang' => $data['golongan_ruang'] ?? null,
                    'unit_kerja' => $data['unit_kerja'] ?? null,
                    'role' => $data['role'] ?? 'pegawai',
                    'tanggal_mulai_kerja' => !empty($data['tanggal_mulai_kerja']) ? $data['tanggal_mulai_kerja'] : null,
                    'status_pegawai' => $data['status_pegawai'] ?? 'aparatur',
                    'email' => !empty($data['email']) ? $data['email'] : $data['nip'] . '@pn-natuna.go.id',
                    'password' => bcrypt($data['nip']),
                    'leave_balance' => 12,
                ]);
                $imported++;
            }

            return redirect()->route('pegawai.index')
                ->with('success', "Import berhasil: {$imported} pegawai ditambahkan. {$skipped} baris dilewati (duplikat/kosong).");
        }

        return redirect()->route('pegawai.index')
            ->with('error', 'Saat ini hanya format CSV yang didukung. Silakan gunakan template CSV.');
    }

    /**
     * Sprint 5 #29: Export pegawai as CSV
     */
    public function export(Request $request)
    {
        $query = User::query()->orderBy('name');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name','like',"%{$s}%")->orWhere('nip','like',"%{$s}%"));
        }
        if ($request->filled('role')) $query->where('role', $request->role);
        if ($request->filled('status_pegawai')) $query->where('status_pegawai', $request->status_pegawai);
        if ($request->boolean('active_only')) $query->where(fn($q) => $q->where('is_active', true)->orWhereNull('is_active'));

        $rows = $query->get();
        $csv  = "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        $csv .= "Nama,NIP,Jabatan,Golongan,Unit Kerja,Role,Status Pegawai,Masa Kerja,Sisa Cuti,Aktif\n";
        foreach ($rows as $p) {
            $aktif = ($p->is_active ?? true) ? 'Ya' : 'Tidak';
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $p->name) . '"',
                '"' . $p->nip . '"',
                '"' . ($p->jabatan ?? '') . '"',
                '"' . ($p->golongan_ruang ?? '') . '"',
                '"' . ($p->unit_kerja ?? '') . '"',
                '"' . $p->role . '"',
                '"' . ($p->status_pegawai ?? '') . '"',
                '"' . ($p->masa_kerja_format ?? '') . '"',
                $p->leave_balance,
                $aktif,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="pegawai-' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Sprint 5 #28: Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $ids    = $request->input('ids', []);
        $action = $request->input('action');
        if (empty($ids)) return back()->with('error', 'Pilih pegawai terlebih dahulu.');

        switch ($action) {
            case 'reset-password':
                foreach ($ids as $id) {
                    $p = User::find($id);
                    if ($p) $p->update(['password' => Hash::make($p->nip)]);
                }
                return back()->with('success', count($ids) . ' password direset ke NIP masing-masing.');

            case 'pindah-unit':
                $unit = $request->input('unit_kerja');
                if (!$unit) return back()->with('error', 'Unit kerja wajib diisi.');
                User::whereIn('id', $ids)->update(['unit_kerja' => $unit]);
                return back()->with('success', count($ids) . ' pegawai dipindahkan ke unit ' . $unit . '.');

            case 'nonaktifkan':
                User::whereIn('id', $ids)->update(['is_active' => false]);
                return back()->with('success', count($ids) . ' pegawai dinonaktifkan.');

            case 'aktifkan':
                User::whereIn('id', $ids)->update(['is_active' => true]);
                return back()->with('success', count($ids) . ' pegawai diaktifkan.');

            default:
                return back()->with('error', 'Aksi tidak dikenali.');
        }
    }

    /**
     * Sprint 5 #31: Inline edit jabatan / unit_kerja
     */
    public function inlineEdit(Request $request, User $pegawai)
    {
        $validated = $request->validate([
            'field' => 'required|in:jabatan,unit_kerja,golongan_ruang',
            'value' => 'nullable|string|max:255',
        ]);
        $pegawai->update([$validated['field'] => $validated['value']]);
        return response()->json(['success' => true, 'value' => $pegawai->{$validated['field']}]);
    }

    /**
     * Sprint 5 #27: Upload photo / avatar
     */
    public function uploadPhoto(Request $request, User $pegawai)
    {
        $request->validate(['photo' => 'required|image|max:2048|mimes:jpg,jpeg,png,webp']);
        if ($pegawai->photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($pegawai->photo);
        }
        $path = $request->file('photo')->store('avatars', 'public');
        $pegawai->update(['photo' => $path]);
        return back()->with('success', 'Foto profil pegawai berhasil diperbarui.');
    }

    /**
     * Sprint 5 #32: Toggle active/non-active
     */
    public function toggleActive(User $pegawai)
    {
        $pegawai->update(['is_active' => !($pegawai->is_active ?? true)]);
        $status = ($pegawai->is_active) ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Pegawai {$pegawai->name} berhasil {$status}.");
    }
}
