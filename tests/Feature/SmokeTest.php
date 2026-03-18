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

    // -------------------------------------------------------------------------
    // Sprint A: Form & Flow Tests
    // -------------------------------------------------------------------------

    public function test_hari_libur_api_returns_json_by_year(): void
    {
        $user = $this->makeUser(); // role pegawai
        $response = $this->actingAs($user)->get('/hari-libur/api?year=2026');
        $response->assertStatus(200);
        $response->assertJsonIsArray();
    }

    public function test_leave_reason_templates_api_returns_json(): void
    {
        $user = $this->makeUser();
        $response = $this->actingAs($user)->get('/leave-reason-templates/api');
        $response->assertStatus(200);
        $response->assertJsonIsArray();
    }

    public function test_leave_reason_templates_admin_can_create(): void
    {
        $admin = $this->makeAdmin();
        $response = $this->actingAs($admin)->post('/admin/leave-reason-templates', [
            'label'      => 'Keperluan Keluarga',
            'body'       => 'Saya perlu menghadiri acara keluarga yang tidak dapat ditunda.',
            'sort_order' => 1,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('leave_reason_templates', ['label' => 'Keperluan Keluarga']);
    }

    public function test_leave_reason_templates_admin_can_delete(): void
    {
        $admin = $this->makeAdmin();
        $tpl = \App\Models\LeaveReasonTemplate::create([
            'label'      => 'Template Hapus',
            'body'       => 'Isi template.',
            'is_active'  => true,
            'sort_order' => 0,
        ]);
        $response = $this->actingAs($admin)->delete("/admin/leave-reason-templates/{$tpl->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('leave_reason_templates', ['id' => $tpl->id]);
    }

    public function test_leave_create_accepts_start_query_param(): void
    {
        $user = $this->makeUser();
        $response = $this->actingAs($user)->get('/leave/create?type=cuti_tahunan&start=2026-03-17');
        $response->assertStatus(200);
    }

    public function test_kalender_loads_with_panel_data(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get('/kalender')->assertStatus(200);
    }

    public function test_leave_select_type_accepts_start_query_param(): void
    {
        $user = $this->makeUser();
        $response = $this->actingAs($user)->get('/leave/select-type?start=2026-03-17');
        $response->assertStatus(200);
    }

    public function test_bulk_pertimbangan_forbidden_for_pegawai(): void
    {
        $pegawai = $this->makeUser(['nip' => '999999999999999991']);
        $this->actingAs($pegawai)
             ->post('/leave/bulk-pertimbangan', ['ids' => [999]])
             ->assertStatus(403);
    }

    public function test_bulk_pertimbangan_updates_status(): void
    {
        $atasan  = $this->makeUser(['role' => 'atasan', 'nip' => '999999999999999992']);
        $pegawai = $this->makeUser(['nip' => '999999999999999993']);

        $leave = \App\Models\LeaveRequest::factory()->create([
            'user_id'            => $pegawai->id,
            'atasan_reviewer_id' => $atasan->id,
            'status'             => \App\Models\LeaveRequest::STATUS_DIAJUKAN,
        ]);

        $this->actingAs($atasan)
             ->post('/leave/bulk-pertimbangan', ['ids' => [$leave->id]])
             ->assertRedirect(route('keputusan.index', ['tab' => 'review']));

        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => \App\Models\LeaveRequest::STATUS_PERTIMBANGAN,
        ]);
    }

    public function test_keputusan_filter_by_name_for_ketua(): void
    {
        $ketua = $this->makeKetua();

        // Buat dua pegawai dengan nama berbeda, keduanya punya leave pertimbangan
        $pegawai1 = $this->makeUser(['name' => 'Zulkifli Harahap', 'nip' => '999999999999999994']);
        $pegawai2 = $this->makeUser(['name' => 'Maria Ningsih',    'nip' => '999999999999999995']);
        \App\Models\LeaveRequest::factory()->create([
            'user_id' => $pegawai1->id,
            'status'  => \App\Models\LeaveRequest::STATUS_PERTIMBANGAN,
        ]);
        \App\Models\LeaveRequest::factory()->create([
            'user_id' => $pegawai2->id,
            'status'  => \App\Models\LeaveRequest::STATUS_PERTIMBANGAN,
        ]);

        // Cari hanya "Zulkifli" — Maria Ningsih seharusnya tidak muncul
        $this->actingAs($ketua)
             ->get('/keputusan?q=Zulkifli')
             ->assertStatus(200)
             ->assertSee('Zulkifli Harahap')
             ->assertDontSee('Maria Ningsih');
    }

    public function test_keputusan_filter_by_name_for_atasan(): void
    {
        $atasan   = $this->makeUser(['role' => 'atasan', 'nip' => '999999999999999996']);
        $pegawai1 = $this->makeUser(['name' => 'Bambang Suharto', 'nip' => '999999999999999997']);
        $pegawai2 = $this->makeUser(['name' => 'Siti Rahayu',     'nip' => '999999999999999998']);

        // Keduanya bawahan atasan ini, status diajukan (muncul di review tab)
        \App\Models\LeaveRequest::factory()->create([
            'user_id'            => $pegawai1->id,
            'atasan_reviewer_id' => $atasan->id,
            'status'             => \App\Models\LeaveRequest::STATUS_DIAJUKAN,
        ]);
        \App\Models\LeaveRequest::factory()->create([
            'user_id'            => $pegawai2->id,
            'atasan_reviewer_id' => $atasan->id,
            'status'             => \App\Models\LeaveRequest::STATUS_DIAJUKAN,
        ]);

        // Filter berdasarkan nama — hanya Bambang yang seharusnya muncul
        $this->actingAs($atasan)
             ->get('/keputusan?tab=review&q=Bambang')
             ->assertStatus(200)
             ->assertSee('Bambang Suharto')
             ->assertDontSee('Siti Rahayu');
    }

    public function test_cuti_tahunan_rejects_more_than_12_working_days(): void
    {
        $user = User::factory()->create([
            'role' => 'pegawai',
            'leave_balance' => 20,
            'masa_kerja_mulai' => now()->subYears(2),
        ]);
        // 3 minggu kalender = sekitar 15-19 hari kerja (> 12 hari kerja limit)
        // addDays(20) ensures >= 5 working days advance notice required for TYPE_TAHUNAN
        $start = now()->addDays(20)->startOfWeek(); // Senin
        $end = $start->copy()->addWeeks(3)->endOfWeek()->subDays(2); // Jumat 3 minggu kemudian

        $response = $this->actingAs($user)->post(route('leave.store'), [
            'type'           => 'cuti_tahunan',
            'start_date'     => $start->format('Y-m-d'),
            'end_date'       => $end->format('Y-m-d'),
            'reason'         => 'Liburan keluarga besar',
            'alamat_cuti'    => 'Jl Test',
            'telepon_cuti'   => '081234567890',
        ]);
        $response->assertSessionHasErrors();
    }

    public function test_cuti_bersama_cek_saldo_saat_pengajuan(): void
    {
        $atasan = User::factory()->create(['role' => 'atasan']);
        $user = User::factory()->create([
            'role'            => 'pegawai',
            'leave_balance'   => 0, // saldo habis
            'masa_kerja_mulai' => now()->subYears(2),
            'atasan_id'       => $atasan->id,
        ]);

        $response = $this->actingAs($user)->post(route('leave.store'), [
            'type'        => 'cuti_bersama',
            'start_date'  => now()->addDays(5)->format('Y-m-d'),
            'end_date'    => now()->addDays(5)->format('Y-m-d'),
            'reason'      => 'Libur bersama nasional',
            'alamat_cuti' => 'Rumah',
            'telepon_cuti' => '081234567890',
        ]);
        $response->assertSessionHasErrors();
    }
}
