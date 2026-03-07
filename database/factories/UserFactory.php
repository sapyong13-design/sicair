<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'             => $this->faker->name(),
            'nip'              => $this->faker->unique()->numerify('19########01####'),
            'email'            => $this->faker->unique()->safeEmail(),
            'password'         => Hash::make('password'),
            'role'             => 'pegawai',
            'jabatan'          => $this->faker->randomElement(['Staf', 'Panitera Muda', 'Panitera Pengganti']),
            'golongan_ruang'   => $this->faker->randomElement(['II/a', 'II/b', 'III/a', 'III/b', 'IV/a']),
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => $this->faker->dateTimeBetween('-20 years', '-1 year')->format('Y-m-d'),
            'status_pegawai'   => 'aparatur',
            'leave_balance'    => 12,
            'is_active'        => true,
            'jenis_kelamin'    => $this->faker->randomElement(['L', 'P']),
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
        ];
    }

    public function admin(): static
    {
        return $this->state([
            'role'    => 'admin',
            'name'    => 'Administrator',
            'jabatan' => 'Administrator',
        ]);
    }

    public function ketua(): static
    {
        return $this->state([
            'role'    => 'ketua',
            'jabatan' => 'Ketua Pengadilan',
        ]);
    }

    public function atasan(): static
    {
        return $this->state([
            'role'    => 'panitera',
            'jabatan' => 'Panitera',
        ]);
    }
}
