<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-6 months', '+1 month');
        $end   = Carbon::instance($start)->addDays(rand(1, 5));

        return [
            'user_id'          => User::factory(),
            'type'             => $this->faker->randomElement([
                LeaveRequest::TYPE_TAHUNAN,
                LeaveRequest::TYPE_SAKIT,
                LeaveRequest::TYPE_MELAHIRKAN,
                LeaveRequest::TYPE_BESAR,
            ]),
            'start_date'       => $start->format('Y-m-d'),
            'end_date'         => $end->format('Y-m-d'),
            'reason'           => $this->faker->sentence(10),
            'status'           => LeaveRequest::STATUS_DIAJUKAN,
            'total_hari_kerja' => rand(1, 5),
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => LeaveRequest::STATUS_DISETUJUI]);
    }

    public function rejected(): static
    {
        return $this->state(['status' => LeaveRequest::STATUS_DITOLAK]);
    }

    public function pending(): static
    {
        return $this->state(['status' => LeaveRequest::STATUS_PERTIMBANGAN]);
    }
}
