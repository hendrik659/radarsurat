<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_public_registration_routes_are_unavailable(): void
    {
        $this->get('/daftar')->assertNotFound();

        $this->post('/daftar', [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.test',
            'password' => 'KataSandi123!',
            'password_confirmation' => 'KataSandi123!',
        ])->assertNotFound();

        $this->assertGuest();
    }

    public function test_an_active_account_can_login(): void
    {
        $user = $this->makeLoginUser();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_an_account_cannot_login_with_an_invalid_password(): void
    {
        $user = $this->makeLoginUser();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'salah-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_an_inactive_account_cannot_login(): void
    {
        $user = $this->makeLoginUser(['is_active' => false]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_an_authenticated_user_can_logout(): void
    {
        $user = $this->makeLoginUser();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

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
