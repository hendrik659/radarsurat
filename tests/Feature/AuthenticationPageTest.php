<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_is_available(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Portal internal')
            ->assertSee('type="password"', false);
    }

    public function test_the_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_the_registration_page_is_available(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Buat akun')
            ->assertSee('name="password_confirmation"', false);
    }

    public function test_a_guest_can_register_and_is_signed_in(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.test',
            'password' => 'KataSandi123!',
            'password_confirmation' => 'KataSandi123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('roles', [
            'name' => 'Pengguna',
            'slug' => 'user',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.test',
            'is_active' => true,
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Radarsurat')
            ->assertSee('Pengguna Baru')
            ->assertSee('Keluar')
            ->assertSee('id="sidebar"', false);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_successful_login_updates_last_login_at(): void
    {
        Carbon::setTestNow('2026-07-26 02:15:00 UTC');
        $user = $this->makeLoginUser();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->last_login_at->equalTo(now()));
    }

    public function test_failed_login_does_not_update_last_login_at(): void
    {
        $user = $this->makeLoginUser(['last_login_at' => '2026-07-25 01:00:00']);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'salah-password',
        ])->assertSessionHasErrors('email');

        $this->assertSame('2026-07-25 01:00:00', $user->fresh()->last_login_at->format('Y-m-d H:i:s'));
        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login_or_update_last_login_at(): void
    {
        $user = $this->makeLoginUser([
            'is_active' => false,
            'last_login_at' => null,
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertNull($user->fresh()->last_login_at);
        $this->assertGuest();
    }

    private function makeLoginUser(array $attributes = []): User
    {
        $role = Role::query()->create([
            'name' => 'Admin Surat',
            'slug' => 'admin_surat',
        ]);

        $user = new User;
        $user->forceFill(array_merge([
            'name' => 'Admin Login',
            'email' => 'admin.login@example.test',
            'password' => Hash::make('password'),
            'role_id' => $role->id,
            'is_active' => true,
        ], $attributes));
        $user->save();

        return $user;
    }
}
