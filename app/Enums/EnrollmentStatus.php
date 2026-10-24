<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Enrolled = 'enrolled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Withdrawn = 'withdrawn';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Enrolled => 'Enrolled',
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
            self::Withdrawn => 'Withdrawn',
        };
    }

    /**
     * Get the badge colour used in the UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::Enrolled => 'sky',
            self::InProgress => 'amber',
            self::Completed => 'green',
            self::Withdrawn => 'red',
        };
    }

    /**
     * Get the enum values.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
