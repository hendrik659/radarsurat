<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_account_manager_can_search_filter_and_paginate_users(): void
    {
        $managerRole = Role::query()->create(['name' => 'Admin Surat', 'slug' => 'admin_surat']);
        $memberRole = Role::query()->create(['name' => 'Anggota Divisi', 'slug' => 'anggota_divisi']);
        $editorial = Division::query()->create(['name' => 'Editorial', 'code' => 'EDT']);
        $marketing = Division::query()->create(['name' => 'Marketing', 'code' => 'MKT']);

        $manager = $this->makeUser($managerRole, 'Rina Manager');
        $matchingUser = $this->makeUser($memberRole, 'Aulia Rahman', [
            'email' => 'aulia@radarsurat.test',
            'employee_number' => 'EMP-001',
            'position' => 'Reporter',
            'division_id' => $editorial->id,
            'is_active' => true,
            'last_login_at' => now()->subHour(),
        ]);
        $this->makeUser($memberRole, 'Budi Tidak Cocok', [
            'division_id' => $marketing->id,
            'is_active' => false,
        ]);

        foreach (range(1, 11) as $number) {
            $this->makeUser($memberRole, 'User '.str_pad((string) $number, 2, '0', STR_PAD_LEFT));
        }

        $this->actingAs($manager)
            ->get(route('users.index', [
                'search' => 'aulia@radarsurat.test',
                'role' => $memberRole->id,
                'division' => $editorial->id,
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertSee($matchingUser->name)
            ->assertSee('EMP-001')
            ->assertSee('Reporter')
            ->assertSee('Anggota Divisi')
            ->assertSee('Editorial')
            ->assertSee('Aktif')
            ->assertDontSee('Budi Tidak Cocok');

        $this->actingAs($manager)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Halaman 1 dari 2');

        $this->actingAs($manager)
            ->get(route('users.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Halaman 2 dari 2')
            ->assertSee('User 11');
    }

    public function test_non_manager_cannot_view_the_user_index(): void
    {
        $memberRole = Role::query()->create(['name' => 'Anggota Divisi', 'slug' => 'anggota_divisi']);
        $member = $this->makeUser($memberRole, 'Anggota Biasa');

        $this->actingAs($member)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_manage_users_without_permanent_delete_routes(): void
    {
        $managerRole = Role::query()->create(['name' => 'Admin Surat', 'slug' => 'admin_surat']);
        $memberRole = Role::query()->create(['name' => 'Anggota Divisi', 'slug' => 'anggota_divisi']);
        $manager = $this->makeUser($managerRole, 'Admin Surat');

        $response = $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Pengguna Baru',
            'email' => 'pengguna.baru@example.test',
            'role_id' => $memberRole->id,
            'division_id' => '',
            'is_active' => '1',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ]);

        $user = User::query()->where('email', 'pengguna.baru@example.test')->firstOrFail();
        $response->assertRedirect(route('users.show', $user));

        $this->actingAs($manager)
            ->get(route('users.show', $user))
            ->assertOk()
            ->assertSee('Anggota Divisi')
            ->assertSee('<dd>-</dd>', false);

        $this->actingAs($manager)
            ->patch(route('users.status', $user), ['is_active' => false])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
            'deleted_at' => null,
        ]);

        $this->assertFalse(collect(app('router')->getRoutes())->contains(
            fn ($route) => $route->getName() === 'users.destroy',
        ));
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $managerRole = Role::query()->create(['name' => 'Admin Surat', 'slug' => 'admin_surat']);
        $manager = $this->makeUser($managerRole, 'Admin Surat');

        $this->actingAs($manager)
            ->patch(route('users.status', $manager), ['is_active' => false])
            ->assertUnprocessable();

        $this->assertTrue($manager->fresh()->is_active);
    }

    public function test_inactive_authenticated_user_is_logged_out(): void
    {
        $managerRole = Role::query()->create(['name' => 'Admin Surat', 'slug' => 'admin_surat']);
        $manager = $this->makeUser($managerRole, 'Admin Nonaktif', ['is_active' => false]);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_last_login_is_displayed_in_wib_and_null_is_informative(): void
    {
        $managerRole = Role::query()->create(['name' => 'Admin Surat', 'slug' => 'admin_surat']);
        $manager = $this->makeUser($managerRole, 'Admin Surat', [
            'last_login_at' => '2026-07-26 02:15:00',
        ]);
        $this->makeUser($managerRole, 'Belum Login', ['last_login_at' => null]);

        $this->actingAs($manager)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('26 Jul 2026, 09:15 WIB')
            ->assertSee('Belum pernah login');

        $this->actingAs($manager)
            ->get(route('users.show', $manager))
            ->assertOk()
            ->assertSee('26 Jul 2026, 09:15 WIB');
    }

    private function makeUser(Role $role, string $name, array $attributes = []): User
    {
        $user = new User;
        $user->forceFill(array_merge([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'password' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ], $attributes));
        $user->save();

        return $user;
    }
}
