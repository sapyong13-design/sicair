<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk approval workflow cuti.
 *
 * Workflow dua level sesuai SEMA 13/2019:
 *   1. Pegawai ajukan → status diajukan
 *   2. Atasan review  → status pertimbangan_atasan
 *   3. Pejabat decide → status disetujui / ditolak
 *
 * Catatan: Route names yang digunakan:
 *   - leave.store  : POST /leave
 *   - leave.review : POST /leave/{leaveRequest}/review  (role: atasan/panitera/sekretaris/ketua/admin)
 *   - leave.decide : POST /leave/{leaveRequest}/decide  (role: ketua/admin)
 *
 * Nilai keputusan pejabat yang diterima controller: 'setuju' | 'ubah' | 'tangguhkan' | 'tolak'
 * Status yang disimpan setelah setuju: 'disetujui'
 * Status yang disimpan setelah tolak : 'ditolak'
 */
class LeaveApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $atasan;
    private User $pegawai;
    private User $ketua;

    protected function setUp(): void
    {
        parent::setUp();

        // atasan() factory state → role: panitera
        $this->atasan = User::factory()->atasan()->create();

        // pegawai default → role: pegawai, status_pegawai: aparatur
        // atasan_id → panitera (bukan ketua), sehingga skipAtasanReview() = false
        // dan status awal pengajuan menjadi STATUS_DIAJUKAN
        $this->pegawai = User::factory()->create([
            'atasan_id'      => $this->atasan->id,
            'leave_balance'  => 12,
            'status_pegawai' => 'aparatur',
        ]);

        // ketua() factory state → role: ketua
        $this->ketua = User::factory()->ketua()->create();
    }

    // =========================================================================
    // Test 1: Atasan dapat memberikan pertimbangan (meneruskan ke pejabat)
    // =========================================================================

    /**
     * Atasan yang memiliki role panitera/atasan dapat me-review pengajuan
     * yang berstatus 'diajukan' dan meneruskannya ke pejabat berwenang.
     */
    public function test_atasan_dapat_review_dan_teruskan_ke_pejabat(): void
    {
        $leave = LeaveRequest::factory()->create([
            'user_id' => $this->pegawai->id,
            'status'  => LeaveRequest::STATUS_DIAJUKAN,
            'type'    => LeaveRequest::TYPE_TAHUNAN,
        ]);

        $response = $this->actingAs($this->atasan)
            ->post(route('leave.review', $leave->id), [
                'pertimbangan'  => 'setuju',
                'catatan_atasan' => '',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leave_requests', [
            'id'                  => $leave->id,
            'status'              => LeaveRequest::STATUS_PERTIMBANGAN,
            'atasan_reviewer_id'  => $this->atasan->id,
            'pertimbangan_atasan' => 'setuju',
        ]);
    }

    /**
     * Atasan yang me-review dengan keputusan 'tolak' langsung mengubah
     * status menjadi ditolak tanpa perlu keputusan pejabat.
     */
    public function test_atasan_dapat_tolak_langsung(): void
    {
        $leave = LeaveRequest::factory()->create([
            'user_id' => $this->pegawai->id,
            'status'  => LeaveRequest::STATUS_DIAJUKAN,
        ]);

        $response = $this->actingAs($this->atasan)
            ->post(route('leave.review', $leave->id), [
                'pertimbangan'   => 'tolak',
                'catatan_atasan' => 'Kebutuhan dinas mendesak.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => LeaveRequest::STATUS_DITOLAK,
        ]);
    }

    // =========================================================================
    // Test 2: Pejabat setujui dan saldo berkurang
    // =========================================================================

    /**
     * Ketika pejabat menyetujui cuti tahunan, saldo cuti pegawai
     * berkurang sebesar total_hari_kerja.
     */
    public function test_pejabat_setujui_cuti_tahunan_dan_saldo_berkurang(): void
    {
        $this->pegawai->update(['leave_balance' => 12]);

        $leave = LeaveRequest::factory()->create([
            'user_id'          => $this->pegawai->id,
            'status'           => LeaveRequest::STATUS_PERTIMBANGAN,
            'type'             => LeaveRequest::TYPE_TAHUNAN,
            'total_hari_kerja' => 3,
        ]);

        $response = $this->actingAs($this->ketua)
            ->post(route('leave.decide', $leave->id), [
                'keputusan'       => 'setuju',
                'catatan_pejabat' => '',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leave_requests', [
            'id'              => $leave->id,
            'status'          => LeaveRequest::STATUS_DISETUJUI,
            'keputusan_pejabat' => 'setuju',
            'pejabat_id'      => $this->ketua->id,
        ]);

        $this->assertEquals(9, $this->pegawai->fresh()->leave_balance);
    }

    // =========================================================================
    // Test 3: Pejabat tolak — saldo tidak berubah
    // =========================================================================

    /**
     * Ketika pejabat menolak pengajuan, saldo cuti pegawai tidak berkurang.
     */
    public function test_pejabat_tolak_dan_saldo_tidak_berubah(): void
    {
        $this->pegawai->update(['leave_balance' => 12]);

        $leave = LeaveRequest::factory()->create([
            'user_id'          => $this->pegawai->id,
            'status'           => LeaveRequest::STATUS_PERTIMBANGAN,
            'type'             => LeaveRequest::TYPE_TAHUNAN,
            'total_hari_kerja' => 3,
        ]);

        $response = $this->actingAs($this->ketua)
            ->post(route('leave.decide', $leave->id), [
                'keputusan'       => 'tolak',
                'catatan_pejabat' => 'Kebutuhan kantor mendesak.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => LeaveRequest::STATUS_DITOLAK,
        ]);

        // Saldo tidak berkurang
        $this->assertEquals(12, $this->pegawai->fresh()->leave_balance);
    }

    // =========================================================================
    // Test 4: Saldo tidak berkurang untuk cuti non-tahunan (misal cuti sakit)
    // =========================================================================

    /**
     * Penyetujuan cuti sakit tidak mengurangi saldo cuti tahunan pegawai.
     */
    public function test_pejabat_setujui_cuti_sakit_tidak_ubah_saldo_tahunan(): void
    {
        $this->pegawai->update(['leave_balance' => 12]);

        $leave = LeaveRequest::factory()->create([
            'user_id'          => $this->pegawai->id,
            'status'           => LeaveRequest::STATUS_PERTIMBANGAN,
            'type'             => LeaveRequest::TYPE_SAKIT,
            'total_hari_kerja' => 5,
        ]);

        $this->actingAs($this->ketua)
            ->post(route('leave.decide', $leave->id), [
                'keputusan'       => 'setuju',
                'catatan_pejabat' => '',
            ]);

        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => LeaveRequest::STATUS_DISETUJUI,
        ]);

        // Saldo cuti tahunan tidak berkurang
        $this->assertEquals(12, $this->pegawai->fresh()->leave_balance);
    }

    // =========================================================================
    // Test 5: Pegawai biasa tidak dapat mengakses route decide (403)
    // =========================================================================

    /**
     * Pegawai dengan role 'pegawai' tidak memiliki akses ke endpoint
     * keputusan pejabat. Middleware CheckRole harus mengembalikan 403.
     */
    public function test_pegawai_biasa_tidak_dapat_akses_route_decide(): void
    {
        $leave = LeaveRequest::factory()->create([
            'user_id' => $this->pegawai->id,
            'status'  => LeaveRequest::STATUS_PERTIMBANGAN,
        ]);

        $response = $this->actingAs($this->pegawai)
            ->post(route('leave.decide', $leave->id), [
                'keputusan' => 'setuju',
            ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // Test 6: Pegawai biasa tidak dapat mengakses route review atasan (403)
    // =========================================================================

    /**
     * Pegawai dengan role 'pegawai' tidak memiliki akses ke endpoint
     * review atasan. Middleware role harus mengembalikan 403.
     */
    public function test_pegawai_biasa_tidak_dapat_akses_route_review(): void
    {
        $leave = LeaveRequest::factory()->create([
            'user_id' => $this->pegawai->id,
            'status'  => LeaveRequest::STATUS_DIAJUKAN,
        ]);

        $pegawai2 = User::factory()->create(['atasan_id' => $this->atasan->id]);

        $response = $this->actingAs($pegawai2)
            ->post(route('leave.review', $leave->id), [
                'pertimbangan' => 'setuju',
            ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // Test 7: Review tidak bisa dilakukan jika status bukan 'diajukan'
    // =========================================================================

    /**
     * Jika status pengajuan sudah 'pertimbangan_atasan' (bukan 'diajukan'),
     * reviewAtasan harus mengembalikan error dan status tidak berubah.
     */
    public function test_atasan_tidak_dapat_review_jika_sudah_pertimbangan(): void
    {
        $leave = LeaveRequest::factory()->create([
            'user_id' => $this->pegawai->id,
            'status'  => LeaveRequest::STATUS_PERTIMBANGAN, // bukan diajukan
        ]);

        $response = $this->actingAs($this->atasan)
            ->post(route('leave.review', $leave->id), [
                'pertimbangan'   => 'setuju',
                'catatan_atasan' => '',
            ]);

        // Controller mengembalikan redirect dengan error flash (back()->with('error'))
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Status tidak berubah
        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => LeaveRequest::STATUS_PERTIMBANGAN,
        ]);
    }

    // =========================================================================
    // Test 8: Decide tidak bisa dilakukan jika status bukan 'pertimbangan_atasan'
    // =========================================================================

    /**
     * Jika status pengajuan belum 'pertimbangan_atasan', decidePejabat
     * harus mengembalikan error dan status tidak berubah.
     */
    public function test_pejabat_tidak_dapat_decide_jika_status_bukan_pertimbangan(): void
    {
        $leave = LeaveRequest::factory()->create([
            'user_id' => $this->pegawai->id,
            'status'  => LeaveRequest::STATUS_DIAJUKAN, // belum dipertimbangkan atasan
        ]);

        $response = $this->actingAs($this->ketua)
            ->post(route('leave.decide', $leave->id), [
                'keputusan'       => 'setuju',
                'catatan_pejabat' => '',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => LeaveRequest::STATUS_DIAJUKAN,
        ]);
    }

    // =========================================================================
    // Test 9: Tangguhkan dan Diubah — status tersimpan dengan benar
    // =========================================================================

    /**
     * Pejabat dapat menangguhkan pengajuan cuti.
     * Status tersimpan sebagai 'ditangguhkan'.
     */
    public function test_pejabat_dapat_tangguhkan_pengajuan(): void
    {
        $leave = LeaveRequest::factory()->create([
            'user_id' => $this->pegawai->id,
            'status'  => LeaveRequest::STATUS_PERTIMBANGAN,
            'type'    => LeaveRequest::TYPE_TAHUNAN,
        ]);

        $this->actingAs($this->ketua)
            ->post(route('leave.decide', $leave->id), [
                'keputusan'       => 'tangguhkan',
                'catatan_pejabat' => 'Menunggu pengganti.',
            ]);

        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => LeaveRequest::STATUS_DITANGGUHKAN,
        ]);
    }

    /**
     * Pejabat dapat mengubah pengajuan cuti.
     * Status tersimpan sebagai 'diubah'.
     */
    public function test_pejabat_dapat_ubah_pengajuan(): void
    {
        $leave = LeaveRequest::factory()->create([
            'user_id' => $this->pegawai->id,
            'status'  => LeaveRequest::STATUS_PERTIMBANGAN,
            'type'    => LeaveRequest::TYPE_TAHUNAN,
        ]);

        $this->actingAs($this->ketua)
            ->post(route('leave.decide', $leave->id), [
                'keputusan'       => 'ubah',
                'catatan_pejabat' => 'Tanggal perlu disesuaikan.',
            ]);

        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => LeaveRequest::STATUS_DIUBAH,
        ]);
    }

    // =========================================================================
    // Test 10: Guest tidak dapat mengakses endpoint approval
    // =========================================================================

    /**
     * Pengguna yang belum login diarahkan ke halaman login
     * saat mencoba mengakses endpoint review atau decide.
     */
    public function test_guest_tidak_dapat_akses_endpoint_approval(): void
    {
        $leave = LeaveRequest::factory()->create([
            'user_id' => $this->pegawai->id,
            'status'  => LeaveRequest::STATUS_PERTIMBANGAN,
        ]);

        $this->post(route('leave.decide', $leave->id), [
            'keputusan' => 'setuju',
        ])->assertRedirect(route('login'));

        $this->post(route('leave.review', $leave->id), [
            'pertimbangan' => 'setuju',
        ])->assertRedirect(route('login'));
    }
}
