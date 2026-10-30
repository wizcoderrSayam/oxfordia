<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Relieved = 'relieved';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Relieved => 'Relieved',
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
            self::Relieved => 'red',
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
