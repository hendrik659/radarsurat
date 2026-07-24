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

    private function makeUser(Role $role, string $name, array $attributes = []): User
    {
        $user = new User();
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
