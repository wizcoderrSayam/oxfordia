<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_is_redirected_to_the_admin_dashboard_after_login(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Dashboard');
    }

    public function test_student_is_redirected_to_the_portal_after_login(): void
    {
        $student = User::factory()->create();

        $this->post('/login', ['email' => $student->email, 'password' => 'password']);

        $this->get('/dashboard')->assertRedirect(route('portal.dashboard'));
    }

    public function test_inactive_users_cannot_log_in(): void
    {
        $user = User::factory()->staff()->inactive()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_users_deactivated_mid_session_are_logged_out(): void
    {
        $staff = User::factory()->staff()->create();
        $this->actingAs($staff);

        $staff->forceFill(['is_active' => false])->save();

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->admin()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_public_registration_only_creates_student_accounts(): void
    {
        $this->post('/register', [
            'name' => 'New Student',
            'email' => 'new@example.com',
            'password' => 'Secret-password-123',
            'password_confirmation' => 'Secret-password-123',
            'role' => 'admin',
        ])->assertRedirect();

        $user = User::query()->where('email', 'new@example.com')->firstOrFail();

        $this->assertSame(Role::Student, $user->role);
        $this->assertFalse($user->canAccessBackOffice());
    }

    public function test_guests_are_redirected_from_admin_routes(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.courses.index'))->assertRedirect(route('login'));
        $this->post(route('admin.courses.store'))->assertRedirect(route('login'));
    }

    public function test_students_cannot_access_admin_routes(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('admin.students.index'))->assertForbidden();
    }

    public function test_back_office_users_are_sent_away_from_the_student_portal(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('portal.dashboard'))->assertRedirect(route('admin.dashboard'));
    }
}
