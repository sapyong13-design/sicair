<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class SystemSettingController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Halaman ini hanya untuk Admin.');
        }

        // Seed defaults if table is empty
        if (SystemSetting::count() === 0) {
            foreach (SystemSetting::defaults() as $item) {
                SystemSetting::create($item);
            }
        }

        $settings = SystemSetting::orderBy('group')->orderBy('id')->get()->groupBy('group');

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $data = $request->input('settings', []);

        // Capture old values before update
        $affectedKeys = array_keys($data);
        $oldSettings = SystemSetting::whereIn('key', $affectedKeys)
            ->pluck('value', 'key')
            ->toArray();

        foreach ($data as $key => $value) {
            SystemSetting::where('key', $key)->update(['value' => $value]);
        }

        // Handle unchecked booleans (checkboxes not sent when unchecked)
        $boolKeys = SystemSetting::where('type', 'boolean')->pluck('key');
        foreach ($boolKeys as $boolKey) {
            if (!array_key_exists($boolKey, $data)) {
                SystemSetting::where('key', $boolKey)->update(['value' => '0']);
            }
        }

        \App\Models\AuditLog::log(
            'update_settings', 'SystemSetting', 0,
            $oldSettings,
            $data,
            'Admin memperbarui pengaturan sistem'
        );

        return redirect()->route('admin.settings')->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function backup()
    {
        $backupDir = storage_path('app/backups');
        $backups = [];

        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/database-*.sqlite') ?: [];
            rsort($files);
            foreach ($files as $f) {
                $bytes = filesize($f);
                $backups[] = [
                    'name' => basename($f),
                    'size' => $bytes > 1048576 ? round($bytes / 1048576, 1) . ' MB' : round($bytes / 1024) . ' KB',
                    'date' => date('d M Y H:i', filemtime($f)),
                ];
            }
        }

        return view('admin.backup', compact('backups'));
    }

    public function runBackup()
    {
        $exitCode = Artisan::call('backup:database', ['--keep' => 14]);
        if ($exitCode !== 0) {
            return redirect()->route('admin.backup')->with('error', 'Backup gagal dibuat. Periksa log server.');
        }
        return redirect()->route('admin.backup')->with('success', 'Backup berhasil dibuat.');
    }

    public function downloadBackup(Request $request)
    {
        $filename = basename($request->query('filename', ''));

        if (empty($filename) || !str_starts_with($filename, 'database-') || !str_ends_with($filename, '.sqlite')) {
            abort(404);
        }

        $path = storage_path('app/backups/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }
}
