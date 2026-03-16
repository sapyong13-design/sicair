<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+1 month');
        $end = (clone $start)->modify('+' . fake()->numberBetween(1, 5) . ' days');

        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement([
                LeaveRequest::TYPE_TAHUNAN,
                LeaveRequest::TYPE_SAKIT,
                LeaveRequest::TYPE_ALASAN_PENTING,
            ]),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'reason' => fake()->sentence(),
            'alamat_cuti' => fake()->address(),
            'telepon_cuti' => fake()->phoneNumber(),
            'status' => 'diajukan',
            'total_hari_kerja' => fake()->numberBetween(1, 5),
        ];
    }
}
