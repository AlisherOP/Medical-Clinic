<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_connect_to_mysql_test_database_and_create_records(): void
    {
        $department = Department::create(['name' => 'Cardiology']);

        $this->assertDatabaseHas('departments', [
            'name' => 'Cardiology',
        ]);

        $user = User::factory()->role('doctor')->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@clinic.test',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@clinic.test',
            'role' => 'doctor',
        ]);
    }
}
