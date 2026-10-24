<?php

namespace Tests\Feature;

use App\Services\NumberSequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NumberSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_numbers_are_sequential_and_unique_per_key(): void
    {
        $numbers = app(NumberSequenceService::class);
        $year = now()->year;

        $this->assertSame("OXF-{$year}-0001", $numbers->nextStudentNumber());
        $this->assertSame("OXF-{$year}-0002", $numbers->nextStudentNumber());
        $this->assertSame("ENR-{$year}-00001", $numbers->nextEnrollmentNumber());
        $this->assertSame("OXF-CERT-{$year}-000001", $numbers->nextCertificateNumber());
    }

    public function test_sequences_restart_each_year(): void
    {
        $numbers = app(NumberSequenceService::class);

        Carbon::setTestNow('2026-12-31 10:00');
        $numbers->nextStudentNumber();
        $this->assertSame('OXF-2026-0002', $numbers->nextStudentNumber());

        Carbon::setTestNow('2027-01-01 10:00');
        $this->assertSame('OXF-2027-0001', $numbers->nextStudentNumber());

        Carbon::setTestNow();
    }
}
