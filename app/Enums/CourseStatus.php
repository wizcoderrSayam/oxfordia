<?php

namespace App\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Archived => 'Archived',
        };
    }

    /**
     * Get the badge colour used in the UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::Published => 'green',
            self::Archived => 'amber',
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
