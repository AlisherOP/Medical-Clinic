<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_loads_for_guests(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('Create your account');
        $response->assertSee('First Name');
        $response->assertSee('Confirm Password');
    }

    public function test_guest_can_register_as_a_patient(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@clinic.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('appointments.index'));
        $this->assertAuthenticated();

        $user = User::where('email', 'jane.doe@clinic.test')->firstOrFail();

        $this->assertSame('patient', $user->role);
        $this->assertNotNull($user->patient);
        $this->assertStringStartsWith('MRN-', $user->patient->mrn);
    }

    public function test_registration_requires_a_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@clinic.test']);

        $response = $this->from('/register')->post('/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'taken@clinic.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->from('/register')->post('/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@clinic.test',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }
}
