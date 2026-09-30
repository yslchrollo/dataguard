<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCreationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@dataguard.test',
            'password' => Hash::make('AdminPass123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_create_admin_account_without_status_field(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'New Administrator',
            'email' => 'newadmin@dataguard.test',
            'password' => 'SecretAdmin123!',
            'role' => 'admin',
            'department' => 'IT Security',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('users.index'));

        $user = User::where('email', 'newadmin@dataguard.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals('New Administrator', $user->name);
        $this->assertEquals('admin', $user->role);
        $this->assertEquals('active', $user->status);
        $this->assertTrue(Hash::check('SecretAdmin123!', $user->password));

        // Test login
        $this->post('/login', [
            'email' => 'newadmin@dataguard.test',
            'password' => 'SecretAdmin123!',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_create_analyst_account_without_status_field(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'New Analyst',
            'email' => 'newanalyst@dataguard.test',
            'password' => 'AnalystPass123!',
            'role' => 'analyst',
            'department' => 'SOC Operations',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('users.index'));

        $user = User::where('email', 'newanalyst@dataguard.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals('New Analyst', $user->name);
        $this->assertEquals('analyst', $user->role);
        $this->assertEquals('active', $user->status);
        $this->assertTrue(Hash::check('AnalystPass123!', $user->password));

        // Test login
        $this->post('/login', [
            'email' => 'newanalyst@dataguard.test',
            'password' => 'AnalystPass123!',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_create_regular_user_account_without_status_field(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Regular Employee',
            'email' => 'employee@dataguard.test',
            'password' => 'EmployeePass123!',
            'role' => 'user',
            'department' => 'Human Resources',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('users.index'));

        $user = User::where('email', 'employee@dataguard.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Regular Employee', $user->name);
        $this->assertEquals('user', $user->role);
        $this->assertEquals('active', $user->status);
        $this->assertTrue(Hash::check('EmployeePass123!', $user->password));

        // Test login
        $this->post('/login', [
            'email' => 'employee@dataguard.test',
            'password' => 'EmployeePass123!',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_existing_users_can_still_login(): void
    {
        $this->post('/login', [
            'email' => 'admin@dataguard.test',
            'password' => 'AdminPass123!',
        ])->assertRedirect(route('dashboard'));
    }
}
