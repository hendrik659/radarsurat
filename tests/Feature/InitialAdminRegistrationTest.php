<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InitialAdminRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_links_to_admin_registration_and_form_is_available_before_an_admin_exists(): void
    {
        $this->makeRole('admin_surat', 'Admin');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Register Admin')
            ->assertSee('href="'.route('register-admin.create').'"', false);

        $this->get(route('register-admin.create'))
            ->assertOk()
            ->assertSee('Register Admin SIRAPI')
            ->assertSee('action="'.route('register-admin.store').'"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertDontSee('name="role_id"', false)
            ->assertDontSee('Registrasi admin awal sudah selesai.');
    }

    public function test_first_admin_registration_assigns_the_fixed_role_and_redirects_to_login(): void
    {
        $adminRole = $this->makeRole('admin_surat', 'Admin');
        $otherRole = $this->makeRole('anggota_divisi', 'Anggota Divisi');

        $response = $this->post(route('register-admin.store'), [
            'name' => 'Admin Pertama',
            'email' => 'admin.pertama@example.test',
            'password' => 'password-aman',
            'password_confirmation' => 'password-aman',
            'role_id' => $otherRole->id,
            'is_active' => false,
        ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Admin berhasil dibuat. Silakan login.');

        $admin = User::query()->where('email', 'admin.pertama@example.test')->firstOrFail();

        $this->assertSame($adminRole->id, $admin->role_id);
        $this->assertNull($admin->division_id);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('password-aman', $admin->password));
        $this->assertGuest();
    }

    public function test_registration_page_hides_the_form_after_an_admin_exists(): void
    {
        $adminRole = $this->makeRole('admin_surat', 'Admin');
        $this->makeUser($adminRole, 'Admin Existing', 'admin.existing@example.test');

        $this->get(route('register-admin.create'))
            ->assertOk()
            ->assertSee('Registrasi admin awal sudah selesai.')
            ->assertSee('Admin sudah tersedia. Silakan login menggunakan akun admin.')
            ->assertDontSee('action="'.route('register-admin.store').'"', false)
            ->assertDontSee('name="password_confirmation"', false)
            ->assertSee('Kembali ke Login');
    }

    public function test_manual_post_is_rejected_after_an_admin_exists(): void
    {
        $adminRole = $this->makeRole('admin_surat', 'Admin');
        $this->makeUser($adminRole, 'Admin Existing', 'admin.existing@example.test');

        $this->post(route('register-admin.store'), [
            'name' => 'Admin Kedua Publik',
            'email' => 'admin.kedua.publik@example.test',
            'password' => 'password-aman',
            'password_confirmation' => 'password-aman',
        ])
            ->assertRedirect(route('register-admin.create'))
            ->assertSessionHas('status', 'Admin sudah tersedia.');

        $this->assertSame(1, User::query()->where('role_id', $adminRole->id)->count());
        $this->assertDatabaseMissing('users', ['email' => 'admin.kedua.publik@example.test']);
    }

    public function test_existing_admin_can_still_create_another_admin_from_internal_user_management(): void
    {
        $adminRole = $this->makeRole('admin_surat', 'Admin');
        $existingAdmin = $this->makeUser($adminRole, 'Admin Existing', 'admin.existing@example.test');

        $response = $this->actingAs($existingAdmin)->post(route('users.store'), [
            'name' => 'Admin Internal',
            'email' => 'admin.internal@example.test',
            'role_id' => $adminRole->id,
            'division_id' => null,
            'is_active' => true,
            'password' => 'password-internal',
            'password_confirmation' => 'password-internal',
        ]);

        $internalAdmin = User::query()->where('email', 'admin.internal@example.test')->firstOrFail();

        $response->assertRedirect(route('users.show', $internalAdmin));
        $this->assertSame($adminRole->id, $internalAdmin->role_id);
        $this->assertSame(2, User::query()->where('role_id', $adminRole->id)->count());
    }

    public function test_regular_user_cannot_create_an_admin_through_internal_user_management(): void
    {
        $adminRole = $this->makeRole('admin_surat', 'Admin');
        $memberRole = $this->makeRole('anggota_divisi', 'Anggota Divisi');
        $member = $this->makeUser($memberRole, 'Anggota Biasa', 'anggota@example.test');

        $this->actingAs($member)->post(route('users.store'), [
            'name' => 'Admin Tidak Sah',
            'email' => 'admin.tidak.sah@example.test',
            'role_id' => $adminRole->id,
            'is_active' => true,
            'password' => 'password-aman',
            'password_confirmation' => 'password-aman',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'admin.tidak.sah@example.test']);
    }

    private function makeRole(string $slug, string $name): Role
    {
        return Role::query()->create(compact('slug', 'name'));
    }

    private function makeUser(Role $role, string $name, string $email): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'role_id' => $role->id,
            'division_id' => null,
            'is_active' => true,
        ]);
    }
}
