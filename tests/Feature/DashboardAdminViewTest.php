<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardAdminViewTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_dashboard_renders_final_sections_in_the_required_order(): void
    {
        Carbon::setTestNow('2026-08-08 10:00:00');
        $admin = $this->makeAdmin('Ulyatul Ula Kilmi');

        $response = $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-testid="dashboard-admin-banner"', false)
            ->assertSee('Sabtu, 8 Agustus 2026')
            ->assertSeeInOrder([
                'Dashboard Admin Surat',
                'Ringkasan administrasi surat internal.',
                'Total Surat Masuk',
                'Baru Diterima',
                'Menunggu Pemeriksaan',
                'Total Surat Keluar',
                'Akses Cepat',
                'Tren Surat (6 Bulan Terakhir)',
                'Surat Masuk Terbaru',
                'Surat Keluar Terbaru',
                'Aktivitas Terbaru',
                'Data Master',
            ]);

        $this->assertSame(4, substr_count($response->getContent(), 'data-testid="dashboard-kpi"'));
        $this->assertSame(7, substr_count($response->getContent(), 'data-testid="dashboard-quick-access"'));
    }

    public function test_all_quick_access_cards_use_existing_routes(): void
    {
        $admin = $this->makeAdmin();
        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        foreach ([
            'incoming-letters.create',
            'incoming-letters.index',
            'outgoing-letters.index',
            'reports.incoming-letters.index',
            'reports.outgoing-letters.index',
            'users.index',
            'divisions.index',
        ] as $routeName) {
            $response->assertSee('href="'.route($routeName).'"', false);
        }

        $response->assertSee('Catat surat masuk baru')
            ->assertSee('Lihat semua surat masuk')
            ->assertSee('Lihat semua surat keluar')
            ->assertSee('Lihat laporan surat masuk')
            ->assertSee('Lihat laporan surat keluar')
            ->assertSee('Kelola data pengguna')
            ->assertSee('Kelola data divisi');
    }

    public function test_chart_recent_panels_activity_master_and_empty_states_render_accessibly(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-testid="dashboard-trend-section"', false)
            ->assertSee('data-dashboard-trend-chart', false)
            ->assertSee('role="img"', false)
            ->assertSee('aria-label="Diagram garis tren Surat Masuk dan Surat Keluar selama enam bulan terakhir"', false)
            ->assertSee('Belum ada Surat Masuk.')
            ->assertSee('Belum ada Surat Keluar.')
            ->assertSee('Belum ada aktivitas surat.')
            ->assertSee('Users Aktif')
            ->assertSee('Users Nonaktif')
            ->assertSee('Divisi Aktif')
            ->assertSee('Total Users')
            ->assertSee('<th scope="col">Perihal</th>', false)
            ->assertSee('<th scope="col">Kode Sistem</th>', false);
    }

    public function test_sidebar_has_only_report_collapses_and_dynamic_profile_logout_on_desktop_and_mobile(): void
    {
        $admin = $this->makeAdmin('Ulyatul Ula Kilmi');
        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $content = $response->getContent();

        $response->assertSee('data-testid="reports-menu-desktop"', false)
            ->assertSee('data-testid="reports-menu-mobile"', false)
            ->assertSee('data-testid="sidebar-profile-desktop"', false)
            ->assertSee('data-testid="sidebar-profile-mobile"', false)
            ->assertSee('Ulyatul Ula Kilmi')
            ->assertSee('Admin Surat')
            ->assertSee('Log Out')
            ->assertSee('Users')
            ->assertSee('Divisions');

        $this->assertSame(2, substr_count($content, 'data-bs-toggle="collapse"'));
        $this->assertSame(2, substr_count($content, '>Log Out<'));
        $this->assertSame(3, substr_count($content, 'action="'.route('logout').'"'));
    }

    public function test_dashboard_omits_prohibited_sections_fake_features_and_outgoing_status(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Perlu Perhatian')
            ->assertDontSee('Terkirim')
            ->assertDontSee('Activity Logs')
            ->assertDontSee('Pengaturan')
            ->assertDontSee('Pusat Bantuan')
            ->assertDontSee('Arsip')
            ->assertDontSee('Tugas Saya')
            ->assertDontSee('Sembunyikan')
            ->assertDontSee('notification')
            ->assertDontSee('Versi 1.0.0');
    }

    public function test_footer_uses_current_year_without_a_fake_version(): void
    {
        Carbon::setTestNow('2026-08-08 10:00:00');
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('© 2026 Radarsurat - Radar Kediri.')
            ->assertDontSee('Versi');
    }

    private function makeAdmin(string $name = 'Admin Dashboard'): User
    {
        $role = Role::query()->create(['name' => 'Admin Surat', 'slug' => 'admin_surat']);

        return User::query()->create([
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}
