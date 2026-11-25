<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads_for_guests(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Welcome back');
        $response->assertSee('Email Address');
        $response->assertSee('Password');
        $response->assertSee('Register');
    }

    public function test_authenticated_user_is_redirected_away_from_login_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('appointments.index'));
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@clinic.test',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@clinic.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('appointments.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_log_in_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@clinic.test',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@clinic.test',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
