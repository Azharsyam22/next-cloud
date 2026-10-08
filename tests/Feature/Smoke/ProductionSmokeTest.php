<?php

namespace Tests\Feature\Smoke;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_health_check_endpoint_responds_ok(): void
    {
        $response = $this->get('/up');
        $response->assertOk();
    }

    public function test_public_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertOk()
            ->assertSee('CloudCampus Storage');
    }

    public function test_auth_pages_render_without_errors(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_unauthenticated_user_is_redirected_to_login_on_protected_routes(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/storage')->assertRedirect('/login');
        $this->get('/trash')->assertRedirect('/login');
        $this->get('/shares/mine')->assertRedirect('/login');
        $this->get('/shares/with-me')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_all_core_pages(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/storage')->assertOk();
        $this->actingAs($user)->get('/trash')->assertOk();
        $this->actingAs($user)->get('/shares/mine')->assertOk();
        $this->actingAs($user)->get('/shares/with-me')->assertOk();
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_user_can_access_admin_dashboard_and_subpages(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/logs')->assertOk();
    }
}
