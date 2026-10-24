<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Staff = 'staff';
    case Student = 'student';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Staff => 'Staff',
            self::Student => 'Student',
        };
    }

    /**
     * Get the badge colour used in the UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::Admin => 'navy',
            self::Staff => 'sky',
            self::Student => 'slate',
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
