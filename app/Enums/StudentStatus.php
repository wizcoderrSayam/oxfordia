<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Graduated = 'graduated';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Graduated => 'Graduated',
        };
    }

    /**
     * Get the badge colour used in the UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Inactive => 'slate',
            self::Graduated => 'navy',
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
