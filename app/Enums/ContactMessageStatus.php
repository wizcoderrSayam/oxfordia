<?php

namespace App\Enums;

enum ContactMessageStatus: string
{
    case New = 'new';
    case Read = 'read';
    case Closed = 'closed';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Read => 'Read',
            self::Closed => 'Closed',
        };
    }

    /**
     * Get the badge colour used in the UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::New => 'sky',
            self::Read => 'slate',
            self::Closed => 'green',
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
