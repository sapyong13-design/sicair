<?php

namespace Tests\Feature;

use App\Models\CutiRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests — pastikan semua halaman utama tidak error 500.
 * Database in-memory (tidak menyentuh data live).
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name'             => 'Test Pegawai',
            'nip'              => '199001012020011001',
            'password'         => bcrypt('password'),
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Staf',
            'golongan_ruang'   => 'III/a',
            'unit_kerja'       => 'Sekretariat',
            'masa_kerja_mulai' => '2020-01-01',
            'status_pegawai'   => 'aparatur',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
        ], $overrides));
    }

    private function makeKetua(): User
    {
        return $this->makeUser([
            'name'   => 'Ketua Test',
            'nip'    => '197001012001011001',
            'role'   => 'ketua',
            'jabatan'=> 'Ketua Pengadilan',
        ]);
    }

    private function makeAdmin(): User
    {
        return $this->makeUser([
            'name'   => 'Admin Test',
            'nip'    => '000000000000000001',
            'role'   => 'admin',
            'jabatan'=> 'Administrator',
        ]);
    }

    private function makeCutiRecord(User $user): CutiRecord
    {
        return CutiRecord::create([
            'user_id'            => $user->id,
            'tahun'              => now()->year,
            'hak_cuti'           => 12,
            'cuti_diambil'       => 0,
            'sisa_cuti'          => 12,
            'carry_over'         => 0,
            'tambahan_terpencil' => 0,
            'keterangan'         => 'Test',
        ]);
    }

    // =========================================================================
    // Auth
    // =========================================================================

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_login_with_valid_credentials_redirects(): void
    {
        $user = $this->makeUser();

        $this->post('/login', [
            'nip'      => $user->nip,
            'password' => 'password',
        ])->assertRedirect('/dashboard');
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $user = $this->makeUser();

        $this->post('/login', [
            'nip'      => $user->nip,
            'password' => 'wrong',
        ])->assertSessionHasErrors();
    }

    public function test_guest_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    // =========================================================================
    // Dashboard
    // =========================================================================

    public function test_dashboard_loads_for_pegawai(): void
    {
        $user = $this->makeUser();
        $ketua = $this->makeKetua();
        $user->update(['atasan_id' => $ketua->id]);
        $this->makeCutiRecord($user);

        $this->actingAs($user)->get('/dashboard')->assertStatus(200);
    }

    public function test_dashboard_loads_for_admin(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get('/dashboard')->assertStatus(200);
    }

    // =========================================================================
    // Pengajuan Cuti
    // =========================================================================

    public function test_leave_create_page_loads(): void
    {
        $user = $this->makeUser();
        $this->makeCutiRecord($user);

        $this->actingAs($user)
            ->get('/leave/create?type=cuti_tahunan')
            ->assertStatus(200);
    }

    public function test_leave_store_creates_leave_request(): void
    {
        $ketua = $this->makeKetua();
        $user  = $this->makeUser(['atasan_id' => $ketua->id]);
        $this->makeCutiRecord($user);

        $response = $this->actingAs($user)->post('/leave', [
            'type'        => LeaveRequest::TYPE_TAHUNAN,
            'start_date'  => now()->addWeekdays(6)->toDateString(),
            'end_date'    => now()->addWeekdays(8)->toDateString(),
            'reason'      => 'Keperluan keluarga',
            'alamat_cuti' => 'Jakarta',
            'telepon_cuti'=> '08123456789',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $user->id,
            'type'    => LeaveRequest::TYPE_TAHUNAN,
        ]);
    }

    public function test_leave_store_fails_if_end_before_start(): void
    {
        $user = $this->makeUser();
        $this->makeCutiRecord($user);

        $this->actingAs($user)->post('/leave', [
            'type'       => LeaveRequest::TYPE_TAHUNAN,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date'   => now()->addDays(3)->toDateString(),
            'reason'     => 'Test',
        ])->assertSessionHasErrors('end_date');
    }

    public function test_leave_show_page_loads(): void
    {
        $ketua = $this->makeKetua();
        $user  = $this->makeUser(['atasan_id' => $ketua->id]);
        $this->makeCutiRecord($user);

        $leave = LeaveRequest::create([
            'user_id'          => $user->id,
            'type'             => LeaveRequest::TYPE_TAHUNAN,
            'start_date'       => now()->addDays(3),
            'end_date'         => now()->addDays(5),
            'reason'           => 'Test',
            'status'           => LeaveRequest::STATUS_PENDING,
            'total_hari_kerja' => 3,
        ]);

        $this->actingAs($user)
            ->get("/leave/{$leave->id}")
            ->assertStatus(200);
    }

    public function test_leave_history_page_loads(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/leave/history')->assertStatus(200);
    }

    public function test_leave_saya_page_loads(): void
    {
        $user = $this->makeUser();
        $this->makeCutiRecord($user);

        $this->actingAs($user)->get('/leave/saya')->assertStatus(200);
    }

    // =========================================================================
    // Kalender
    // =========================================================================

    public function test_kalender_page_loads(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get('/kalender')->assertStatus(200);
    }

    // =========================================================================
    // Admin — Pegawai
    // =========================================================================

    public function test_pegawai_index_loads_for_admin(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get('/pegawai')->assertStatus(200);
    }

    public function test_pegawai_index_blocked_for_pegawai(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get('/pegawai')->assertStatus(403);
    }

    // =========================================================================
    // Admin — Laporan
    // =========================================================================

    public function test_laporan_tahunan_loads_for_admin(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get('/laporan-tahunan')->assertStatus(200);
    }

    public function test_laporan_saldo_cuti_loads_for_admin(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get('/laporan-saldo-cuti')->assertStatus(200);
    }

    // =========================================================================
    // Admin — Hari Libur
    // =========================================================================

    public function test_hari_libur_index_loads_for_admin(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get('/hari-libur')->assertStatus(200);
    }

    // =========================================================================
    // Admin — Settings & Backup
    // =========================================================================

    public function test_admin_settings_loads(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get('/admin/settings')->assertStatus(200);
    }

    public function test_admin_backup_loads(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get('/admin/backup')->assertStatus(200);
    }

    // =========================================================================
    // Analytics
    // =========================================================================

    public function test_analytics_loads_for_admin(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get('/analytics')->assertStatus(200);
    }

    // =========================================================================
    // Notifikasi
    // =========================================================================

    public function test_notifications_page_loads(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get('/notifications')->assertStatus(200);
    }

    public function test_notifications_unread_count_returns_json(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)
            ->getJson('/notifications/unread-count')
            ->assertStatus(200)
            ->assertJsonStructure(['count']);
    }

    // =========================================================================
    // Profil
    // =========================================================================

    public function test_profile_page_loads(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get('/profile')->assertStatus(200);
    }

    // =========================================================================
    // Health Check
    // =========================================================================

    public function test_health_endpoint_returns_json(): void
    {
        $this->get('/health')
            ->assertJsonStructure(['status', 'checks']);
    }

    // =========================================================================
    // Keputusan
    // =========================================================================

    public function test_keputusan_redirects_for_guest(): void
    {
        $this->get('/keputusan')->assertRedirect('/login');
    }

    public function test_keputusan_loads_for_pegawai(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get('/keputusan')->assertStatus(200);
    }

    public function test_keputusan_loads_for_ketua(): void
    {
        $ketua = $this->makeKetua();
        $this->actingAs($ketua)->get('/keputusan')->assertStatus(200);
    }

    public function test_keputusan_loads_for_atasan(): void
    {
        $atasan = $this->makeUser(['role' => 'sekretaris', 'nip' => '199001012020011002']);
        $this->actingAs($atasan)->get('/keputusan')->assertStatus(200);
    }

    public function test_keputusan_loads_for_admin(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get('/keputusan')->assertStatus(200);
    }

    public function test_keputusan_tab_riwayat_loads_for_ketua(): void
    {
        $ketua = $this->makeKetua();
        $this->actingAs($ketua)->get('/keputusan?tab=riwayat')->assertStatus(200);
    }

    public function test_keputusan_tab_pengajuan_loads_for_atasan(): void
    {
        $atasan = $this->makeUser(['role' => 'sekretaris', 'nip' => '199001012020011003']);
        $this->actingAs($atasan)->get('/keputusan?tab=pengajuan')->assertStatus(200);
    }

    public function test_keputusan_filter_by_type(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)
            ->get('/keputusan?type=cuti_tahunan')
            ->assertStatus(200);
    }
}
