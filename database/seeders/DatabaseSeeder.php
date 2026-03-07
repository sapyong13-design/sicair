<?php

namespace Database\Seeders;

use App\Models\HariLibur;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::firstOrCreate(['nip' => '000000000000000000'], [
            'name'             => 'Administrator',
            'email'            => 'admin@pn-natuna.go.id',
            'password'         => Hash::make('password'),
            'role'             => 'admin',
            'jabatan'          => 'Administrator',
            'golongan_ruang'   => 'IV/a',
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2010-01-01',
            'status_pegawai'   => 'aparatur',
            'leave_balance'    => 12,
            'is_active'        => true,
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
        ]);

        // Ketua
        $ketua = User::firstOrCreate(['nip' => '197001010000000001'], [
            'name'             => 'Ketua Pengadilan',
            'email'            => 'ketua@pn-natuna.go.id',
            'password'         => Hash::make('password'),
            'role'             => 'ketua',
            'jabatan'          => 'Ketua Pengadilan',
            'golongan_ruang'   => 'IV/b',
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2005-01-01',
            'status_pegawai'   => 'hakim',
            'leave_balance'    => 12,
            'is_active'        => true,
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 2,
            'lokasi_terpencil' => false,
        ]);

        // 2 Atasan (Panitera / Sekretaris)
        $atasan1 = User::factory()->atasan()->create([
            'name'     => 'Panitera Kepala',
            'atasan_id'=> $ketua->id,
        ]);
        $atasan2 = User::factory()->atasan()->create([
            'name'     => 'Sekretaris',
            'role'     => 'sekretaris',
            'jabatan'  => 'Sekretaris',
            'atasan_id'=> $ketua->id,
        ]);

        // 10 Pegawai
        $pegawai = User::factory(10)->create([
            'atasan_id' => $atasan1->id,
        ]);

        // Hari Libur Nasional
        HariLibur::insert([
            [
                'tanggal'         => '2026-01-01',
                'keterangan'      => 'Tahun Baru',
                'tahun'           => 2026,
                'is_cuti_bersama' => false,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'tanggal'         => '2026-01-27',
                'keterangan'      => "Isra Mi'raj",
                'tahun'           => 2026,
                'is_cuti_bersama' => false,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'tanggal'         => '2026-03-31',
                'keterangan'      => 'Idul Fitri',
                'tahun'           => 2026,
                'is_cuti_bersama' => false,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);

        // Leave requests in various statuses for each pegawai
        foreach ($pegawai as $p) {
            LeaveRequest::factory()->approved()->create(['user_id' => $p->id]);
            LeaveRequest::factory()->rejected()->create(['user_id' => $p->id]);
            LeaveRequest::factory()->create(['user_id' => $p->id]); // diajukan
        }
    }
}
