<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_saldo_berkurang_saat_cuti_disetujui(): void
    {
        $ketua = User::factory()->ketua()->create();
        $user  = User::factory()->create(['leave_balance' => 12]);
        $leave = LeaveRequest::factory()->create([
            'user_id'          => $user->id,
            'type'             => LeaveRequest::TYPE_TAHUNAN,
            'status'           => LeaveRequest::STATUS_PERTIMBANGAN,
            'total_hari_kerja' => 5,
        ]);

        $this->actingAs($ketua)->post(route('leave.decide', $leave->id), [
            'keputusan' => 'setuju',
        ]);

        $this->assertEquals(7, $user->fresh()->leave_balance);
    }

    public function test_saldo_tidak_berubah_saat_cuti_ditolak(): void
    {
        $ketua = User::factory()->ketua()->create();
        $user  = User::factory()->create(['leave_balance' => 12]);
        $leave = LeaveRequest::factory()->create([
            'user_id'          => $user->id,
            'type'             => LeaveRequest::TYPE_TAHUNAN,
            'status'           => LeaveRequest::STATUS_PERTIMBANGAN,
            'total_hari_kerja' => 5,
        ]);

        $this->actingAs($ketua)->post(route('leave.decide', $leave->id), [
            'keputusan'       => 'tolak',
            'catatan_pejabat' => 'Tidak memenuhi syarat.',
        ]);

        $this->assertEquals(12, $user->fresh()->leave_balance);
    }

    public function test_saldo_tidak_bisa_negatif(): void
    {
        $ketua = User::factory()->ketua()->create();
        $user  = User::factory()->create(['leave_balance' => 2]);
        $leave = LeaveRequest::factory()->create([
            'user_id'          => $user->id,
            'type'             => LeaveRequest::TYPE_TAHUNAN,
            'status'           => LeaveRequest::STATUS_PERTIMBANGAN,
            'total_hari_kerja' => 5, // lebih dari saldo
        ]);

        $this->actingAs($ketua)->post(route('leave.decide', $leave->id), [
            'keputusan' => 'setuju',
        ]);

        $this->assertGreaterThanOrEqual(0, $user->fresh()->leave_balance);
    }
}
