<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'type', 'group'];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) return $default;
        if ($setting->type === 'boolean') return (bool) $setting->value;
        if ($setting->type === 'number') return (int) $setting->value;
        return $setting->value;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
    }

    /**
     * Default settings seeded on first load.
     */
    public static function defaults(): array
    {
        return [
            ['key' => 'max_cuti_tahunan',    'value' => '12',  'label' => 'Maks. Hari Cuti Tahunan',     'type' => 'number',  'group' => 'cuti'],
            ['key' => 'max_cuti_sakit',       'value' => '14',  'label' => 'Maks. Hari Cuti Sakit',        'type' => 'number',  'group' => 'cuti'],
            ['key' => 'reminder_hari_sebelum','value' => '2',   'label' => 'Reminder Setelah Berapa Hari', 'type' => 'number',  'group' => 'notifikasi'],
            ['key' => 'whatsapp_enabled',     'value' => '1',   'label' => 'Aktifkan Notif WhatsApp',      'type' => 'boolean', 'group' => 'notifikasi'],
            ['key' => 'nama_instansi',        'value' => 'Pengadilan Negeri Natuna', 'label' => 'Nama Instansi', 'type' => 'text', 'group' => 'umum'],
            ['key' => 'alamat_instansi',      'value' => 'Jl. Batu Sisir No.1, Ranai, Natuna', 'label' => 'Alamat Instansi', 'type' => 'textarea', 'group' => 'umum'],
        ];
    }
}
