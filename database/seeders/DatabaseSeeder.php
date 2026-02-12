<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin SiHEALING',
            'nip' => '199001012020011001',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'leave_balance' => 12,
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'nip' => '199205152021011002',
            'password' => Hash::make('pegawai123'),
            'role' => 'pegawai',
            'leave_balance' => 12,
        ]);

        User::create([
            'name' => 'Siti Rahayu',
            'nip' => '199308202022012003',
            'password' => Hash::make('pegawai123'),
            'role' => 'pegawai',
            'leave_balance' => 12,
        ]);
    }
}
