<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
    }
}
