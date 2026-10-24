<?php

namespace App\Enums;

enum ResultStatus: string
{
    case Pass = 'pass';
    case Fail = 'fail';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pass => 'Pass',
            self::Fail => 'Fail',
        };
    }

    /**
     * Get the badge colour used in the UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pass => 'green',
            self::Fail => 'red',
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
