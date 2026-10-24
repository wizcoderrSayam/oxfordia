<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Converted = 'converted';
    case Closed = 'closed';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Converted => 'Converted',
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
            self::Contacted => 'amber',
            self::Converted => 'green',
            self::Closed => 'slate',
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
