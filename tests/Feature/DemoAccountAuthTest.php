<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAccountAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_demo_accounts_exist_with_bcrypt_passwords_and_active_status(): void
    {
        $demoUsers = [
            'admin@dataguard.corp' => 'admin',
            'analyst@dataguard.corp' => 'analyst',
            'user@dataguard.corp' => 'user',
        ];

        foreach ($demoUsers as $email => $expectedRole) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user, "Demo account {$email} must exist.");
            $this->assertEquals($expectedRole, $user->role);
            $this->assertEquals('active', $user->status);
            $this->assertTrue($user->isActive());
            $this->assertTrue(Hash::check('password', $user->password), "Password for {$email} must verify via Bcrypt.");
        }
    }

    public function test_admin_demo_can_login_and_access_admin_sections(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@dataguard.corp',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('admin', auth()->user()->role);

        // Can access Admin-only sections
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/policies')->assertOk();
        $this->get('/admin/categories')->assertOk();
        $this->get('/reports')->assertOk();
    }

    public function test_analyst_demo_can_login_and_has_proper_role_boundaries(): void
    {
        $response = $this->post('/login', [
            'email' => 'analyst@dataguard.corp',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('analyst', auth()->user()->role);

        // Analyst can access dashboard and reports
        $this->get('/dashboard')->assertOk();
        $this->get('/reports')->assertOk();

        // Analyst CANNOT access Admin-only management pages (403 Forbidden)
        $this->get('/admin/users')->assertStatus(403);
        $this->get('/admin/policies')->assertStatus(403);
        $this->get('/admin/categories')->assertStatus(403);
    }

    public function test_regular_user_demo_can_login_and_cannot_access_privileged_sections(): void
    {
        $response = $this->post('/login', [
            'email' => 'user@dataguard.corp',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('user', auth()->user()->role);

        // User can access dashboard and submit transfers
        $this->get('/dashboard')->assertOk();
        $this->get('/transfers/create')->assertOk();

        // User CANNOT access Admin or Analyst sections
        $this->get('/reports')->assertStatus(403);
        $this->get('/admin/users')->assertStatus(403);
        $this->get('/admin/policies')->assertStatus(403);
        $this->get('/admin/categories')->assertStatus(403);
    }

    public function test_demo_quick_login_for_all_roles(): void
    {
        // 1. Admin Quick Switch
        $adminResp = $this->get(route('login.quick', 'admin'));
        $adminResp->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('admin', auth()->user()->role);
        $this->assertEquals('admin@dataguard.corp', auth()->user()->email);

        // 2. Analyst Quick Switch
        $analystResp = $this->get(route('login.quick', 'analyst'));
        $analystResp->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('analyst', auth()->user()->role);
        $this->assertEquals('analyst@dataguard.corp', auth()->user()->email);

        // 3. User Quick Switch
        $userResp = $this->get(route('login.quick', 'user'));
        $userResp->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('user', auth()->user()->role);
        $this->assertEquals('user@dataguard.corp', auth()->user()->email);
    }

    public function test_demo_quick_login_rejects_invalid_roles(): void
    {
        $response = $this->get(route('login.quick', 'superadmin'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Invalid demonstration role.');
    }

    public function test_inactive_accounts_are_blocked_from_logging_in(): void
    {
        $response = $this->post('/login', [
            'email' => 'marcus.vance@external.com',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'This account has been deactivated. Contact an administrator.');
        $this->assertGuest();
    }

    public function test_invalid_password_is_rejected(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@dataguard.corp',
            'password' => 'wrong-password-123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_clears_session(): void
    {
        $this->actingAs(User::where('email', 'admin@dataguard.corp')->first());
        $this->assertAuthenticated();

        $response = $this->post(route('logout'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
