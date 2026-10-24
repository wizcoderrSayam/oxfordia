<?php

namespace Tests\Feature;

use App\Enums\CertificateStatus;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Notifications\CertificateIssued;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    private function issue(Enrollment $enrollment): TestResponse
    {
        return $this->post(route('admin.certificates.store', $enrollment), ['issue_date' => now()->toDateString()]);
    }

    public function test_completed_passed_enrollment_receives_a_certificate(): void
    {
        $student = Student::factory()->create(['email' => 'grad@example.com']);
        $enrollment = Enrollment::factory()->passed('A')->create(['student_id' => $student->id]);

        $this->issue($enrollment)->assertRedirect();

        $certificate = Certificate::query()->firstOrFail();
        $this->assertSame('OXF-CERT-'.now()->year.'-000001', $certificate->certificate_number);
        $this->assertSame(32, strlen($certificate->verification_identifier));
        $this->assertSame($student->full_name, $certificate->student_name_snapshot);
        $this->assertSame($enrollment->course->title, $certificate->course_title_snapshot);
        $this->assertSame('A', $certificate->grade_snapshot);
        $this->assertSame($this->admin->id, $certificate->issued_by);
        $this->assertDatabaseHas('activity_logs', ['action' => 'certificate.issued', 'subject_id' => $certificate->id]);
        Notification::assertSentOnDemand(CertificateIssued::class);
    }

    public function test_ineligible_enrollments_cannot_receive_certificates(): void
    {
        $this->issue(Enrollment::factory()->create())->assertSessionHas('error');
        $this->issue(Enrollment::factory()->withdrawn()->create())->assertSessionHas('error');
        $this->issue(Enrollment::factory()->failed()->create())->assertSessionHas('error');

        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_duplicate_issuance_for_the_same_enrollment_is_prevented(): void
    {
        $enrollment = Enrollment::factory()->passed()->create();

        $this->issue($enrollment);
        $this->issue($enrollment)->assertSessionHas('error');

        $this->assertDatabaseCount('certificates', 1);
    }

    public function test_certificate_numbers_and_identifiers_are_unique(): void
    {
        foreach (range(1, 3) as $i) {
            $this->issue(Enrollment::factory()->passed()->create());
        }

        $this->assertSame(3, Certificate::query()->distinct()->count('certificate_number'));
        $this->assertSame(3, Certificate::query()->distinct()->count('verification_identifier'));
    }

    public function test_valid_certificate_verifies_publicly_without_private_data(): void
    {
        $student = Student::factory()->create(['phone' => '9999911111', 'email' => 'private@example.com', 'internal_notes' => 'Secret note']);
        $enrollment = Enrollment::factory()->passed()->create(['student_id' => $student->id]);
        $this->issue($enrollment);
        $certificate = Certificate::query()->firstOrFail();
        auth()->logout();

        foreach ([
            route('certificates.verify.show', $certificate->verification_identifier),
            route('certificates.verify', ['number' => $certificate->certificate_number]),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Certificate Verified')
                ->assertSee($student->full_name)
                ->assertSee($enrollment->course->title)
                ->assertDontSee('9999911111')
                ->assertDontSee('private@example.com')
                ->assertDontSee('Secret note');
        }
    }

    public function test_unknown_certificates_are_reported_invalid(): void
    {
        $this->get(route('certificates.verify.show', 'does-not-exist'))
            ->assertNotFound()
            ->assertSee('Certificate Invalid or Revoked');

        $this->get(route('certificates.verify', ['number' => 'OXF-CERT-1999-999999']))
            ->assertNotFound();
    }

    public function test_revoked_certificates_show_as_revoked(): void
    {
        $this->issue(Enrollment::factory()->passed()->create());
        $certificate = Certificate::query()->firstOrFail();

        $this->post(route('admin.certificates.revoke', $certificate), ['reason' => 'Issued in error'])->assertRedirect();

        $certificate->refresh();
        $this->assertSame(CertificateStatus::Revoked, $certificate->status);
        $this->assertNotNull($certificate->revoked_at);
        $this->assertDatabaseHas('activity_logs', ['action' => 'certificate.revoked', 'subject_id' => $certificate->id]);

        $this->get(route('certificates.verify.show', $certificate->verification_identifier))
            ->assertOk()
            ->assertSee('Certificate Invalid or Revoked')
            ->assertSee('revoked')
            ->assertDontSee('Certificate Verified');
    }

    public function test_revocation_requires_a_reason(): void
    {
        $this->issue(Enrollment::factory()->passed()->create());

        $this->post(route('admin.certificates.revoke', Certificate::query()->firstOrFail()), ['reason' => ''])
            ->assertSessionHasErrors('reason');
    }

    public function test_a_replacement_can_be_issued_after_revocation(): void
    {
        $enrollment = Enrollment::factory()->passed()->create();
        $this->issue($enrollment);
        $original = Certificate::query()->firstOrFail();
        $this->post(route('admin.certificates.revoke', $original), ['reason' => 'Name misspelt']);

        $this->issue($enrollment)->assertRedirect();

        $replacement = Certificate::query()->latest('id')->firstOrFail();
        $this->assertNotSame($original->id, $replacement->id);
        $this->assertSame($original->id, $replacement->replaces_certificate_id);
        $this->assertTrue($replacement->isValid());
    }

    public function test_certificate_preview_pdf_and_print_are_available(): void
    {
        $this->issue(Enrollment::factory()->passed()->create());
        $certificate = Certificate::query()->firstOrFail();

        $this->get(route('admin.certificates.show', $certificate))->assertOk()->assertSee($certificate->certificate_number);
        $this->get(route('admin.certificates.print', $certificate))->assertOk()->assertSee('Print certificate');

        $pdf = $this->get(route('admin.certificates.pdf', $certificate));
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_students_can_only_download_their_own_valid_certificates(): void
    {
        $owner = User::factory()->create();
        $student = Student::factory()->create();
        $student->user()->associate($owner)->save();
        $this->issue(Enrollment::factory()->passed()->create(['student_id' => $student->id]));
        $certificate = Certificate::query()->firstOrFail();

        $this->actingAs($owner)->get(route('portal.certificates.download', $certificate))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('portal.certificates.download', $certificate))->assertNotFound();
    }

    public function test_verification_endpoint_is_rate_limited(): void
    {
        auth()->logout();

        foreach (range(1, 10) as $attempt) {
            $this->get(route('certificates.verify.show', 'guess'.$attempt));
        }

        $this->get(route('certificates.verify.show', 'one-more'))->assertStatus(429);
    }
}
