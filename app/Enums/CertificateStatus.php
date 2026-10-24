<?php

namespace App\Enums;

enum CertificateStatus: string
{
    case Valid = 'valid';
    case Revoked = 'revoked';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Valid => 'Valid',
            self::Revoked => 'Revoked',
        };
    }

    /**
     * Get the badge colour used in the UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::Valid => 'green',
            self::Revoked => 'red',
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
