<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\PortalInvitation;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_manage_academic_records(): void
    {
        $this->actingAs(User::factory()->staff()->create());

        $this->get(route('admin.courses.index'))->assertOk();
        $this->get(route('admin.students.index'))->assertOk();
        $this->get(route('admin.enrollments.index'))->assertOk();
        $this->get(route('admin.certificates.index'))->assertOk();
    }

    public function test_staff_cannot_reach_admin_only_areas(): void
    {
        $this->actingAs(User::factory()->staff()->create());

        $this->get(route('admin.settings.edit'))->assertForbidden();
        $this->put(route('admin.settings.update'), [])->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->post(route('admin.users.store'), [])->assertForbidden();
        $this->get(route('admin.activity.index'))->assertForbidden();
    }

    public function test_staff_cannot_issue_certificates(): void
    {
        $enrollment = Enrollment::factory()->passed()->create();

        $this->actingAs(User::factory()->staff()->create())
            ->post(route('admin.certificates.store', $enrollment))
            ->assertForbidden();

        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_staff_cannot_revoke_certificates(): void
    {
        Notification::fake();
        $certificate = $this->issueCertificate();

        $this->actingAs(User::factory()->staff()->create())
            ->post(route('admin.certificates.revoke', $certificate), ['reason' => 'Trying to revoke'])
            ->assertForbidden();

        $this->assertTrue($certificate->fresh()->isValid());
    }

    public function test_students_cannot_issue_or_revoke_certificates(): void
    {
        Notification::fake();
        $certificate = $this->issueCertificate();
        $student = User::factory()->create();

        $this->actingAs($student)->post(route('admin.certificates.revoke', $certificate), ['reason' => 'Nope nope'])->assertForbidden();
        $this->actingAs($student)->post(route('admin.certificates.store', $certificate->enrollment))->assertForbidden();
    }

    public function test_admin_cannot_remove_the_last_active_administrator(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), ['name' => $admin->name, 'email' => $admin->email, 'role' => 'staff', 'is_active' => 1])
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_admin_can_create_staff_accounts_that_receive_an_invitation(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), ['name' => 'Staff Member', 'email' => 'staff@example.com', 'role' => 'staff'])
            ->assertRedirect(route('admin.users.index'));

        $staff = User::query()->where('email', 'staff@example.com')->firstOrFail();
        $this->assertTrue($staff->isStaff());
        Notification::assertSentTo($staff, PortalInvitation::class);
    }

    private function issueCertificate(): Certificate
    {
        return app(CertificateService::class)->issue(
            Enrollment::factory()->passed()->create(),
            User::factory()->admin()->create(),
        );
    }
}
