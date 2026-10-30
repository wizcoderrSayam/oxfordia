<?php

namespace App\Services;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;

/**
 * Generates human readable, gap-tolerant sequential numbers such as OXF-2026-0001.
 */
class NumberSequenceService
{
    public function nextStudentNumber(): string
    {
        return $this->next('student', config('institute.prefixes.student'), 4);
    }

    public function nextEmployeeNumber(): string
    {
        return $this->next('employee', config('institute.prefixes.employee'), 4);
    }

    public function nextEnrollmentNumber(): string
    {
        return $this->next('enrollment', config('institute.prefixes.enrollment'), 5);
    }

    public function nextCertificateNumber(): string
    {
        return $this->next('certificate', config('institute.prefixes.certificate'), 6);
    }

    public function nextOrderNumber(): string
    {
        return $this->next('order', config('institute.prefixes.order'), 5);
    }

    /**
     * Reserve the next value for the key in the current year.
     */
    public function next(string $key, string $prefix, int $padding): string
    {
        $year = (int) now()->format('Y');

        $value = DB::transaction(function () use ($key, $year): int {
            NumberSequence::query()->insertOrIgnore([
                'key' => $key,
                'year' => $year,
                'last_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = NumberSequence::query()
                ->where('key', $key)
                ->where('year', $year)
                ->lockForUpdate()
                ->firstOrFail();

            $sequence->increment('last_value');

            return $sequence->last_value;
        });

        return sprintf('%s-%d-%s', $prefix, $year, str_pad((string) $value, $padding, '0', STR_PAD_LEFT));
    }
}
