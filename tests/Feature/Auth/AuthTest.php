<?php

namespace Tests\Feature\Auth;

use App\Models\ClinicModel;
use App\Models\RoleModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $clinic     = ClinicModel::factory()->create();
        $role       = RoleModel::factory()->for($clinic)->admin()->create();
        $this->user = User::factory()->withRole($role)->create([
            'email'    => 'test@clinic.com',
            'password' => Hash::make('secret123'),
        ]);
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_valid_credentials_redirect_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'email'    => 'test@clinic.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_wrong_password_fails_login(): void
    {
        $response = $this->post('/login', [
            'email'    => 'test@clinic.com',
            'password' => 'wrongpass',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_unknown_email_fails_login(): void
    {
        $response = $this->post('/login', [
            'email'    => 'nobody@example.com',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_login_requires_email(): void
    {
        $response = $this->post('/login', ['password' => 'secret123']);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_requires_password(): void
    {
        $response = $this->post('/login', ['email' => 'test@clinic.com']);

        $response->assertSessionHasErrors('password');
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    // ── Protected routes ──────────────────────────────────────────────────────

    public function test_unauthenticated_access_to_dashboard_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_access_to_patients_redirects_to_login(): void
    {
        $response = $this->get('/patients');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_reach_dashboard(): void
    {
        app()->instance('currentClinic', $this->user->clinic);

        $response = $this->actingAs($this->user)->get('/');

        $response->assertStatus(200);
    }
}
