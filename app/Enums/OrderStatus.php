<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Failed => 'Failed',
        };
    }

    /**
     * Get the badge colour used in the UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Paid => 'green',
            self::Failed => 'red',
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
