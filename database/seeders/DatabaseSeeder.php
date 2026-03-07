<?php

namespace Database\Seeders;

use App\Models\HariLibur;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::factory()->admin()->create([
            'name'  => 'Administrator',
            'nip'   => '000000000000000000',
            'email' => 'admin@pn-natuna.go.id',
        ]);

        // Ketua
        $ketua = User::factory()->ketua()->create([
            'name' => 'Ketua Pengadilan',
            'nip'  => '197001010000000001',
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
