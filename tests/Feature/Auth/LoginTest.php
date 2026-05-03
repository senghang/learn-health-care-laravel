<?php

namespace Tests\Feature\Auth;

use App\Models\ClinicModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // ── Login page ────────────────────────────────────────────────────────────

    public function test_login_page_loads(): void
    {
        $clinic = $this->bindClinic();

        $response = $this->get($this->clinicUrl('/login'));

        $response->assertStatus(200);
    }

    public function test_already_authenticated_user_is_redirected_from_login(): void
    {
        $this->loginUser();

        $response = $this->get($this->clinicUrl('/login'));

        // AuthController::login() redirects to 'dashboard' when already logged in
        $response->assertRedirect();
    }

    // ── Login submission ──────────────────────────────────────────────────────

    public function test_user_can_login_with_valid_credentials(): void
    {
        $clinic = $this->bindClinic();

        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'email'     => 'doctor@example.com',
            'password'  => Hash::make('secret123'),
            'is_active' => true,
        ]);

        $response = $this->post($this->clinicUrl('/login'), [
            'email'    => 'doctor@example.com',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect();
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $clinic = $this->bindClinic();

        User::factory()->create([
            'clinic_id' => $clinic->id,
            'email'     => 'doctor@example.com',
            'password'  => Hash::make('correct-password'),
        ]);

        $response = $this->post($this->clinicUrl('/login'), [
            'email'    => 'doctor@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(); // back() redirect
        $response->assertSessionHas('error');
    }

    public function test_login_fails_with_missing_email(): void
    {
        $this->bindClinic();

        $response = $this->post($this->clinicUrl('/login'), [
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_fails_with_missing_password(): void
    {
        $this->bindClinic();

        $response = $this->post($this->clinicUrl('/login'), [
            'email' => 'doctor@example.com',
        ]);

        $response->assertSessionHasErrors('password');
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $this->loginUser();

        $response = $this->post($this->clinicUrl('/logout'));

        $this->assertGuest();
        $response->assertRedirect();
    }
}
