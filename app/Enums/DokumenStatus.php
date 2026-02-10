<?php

namespace App\Enums;

enum DokumenStatus: string
{
    case Lidik = 'lidik';
    case Sidik = 'sidik';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Lidik->value => 'Lidik',
            self::Sidik->value => 'Sidik',
        ];
    }
}
