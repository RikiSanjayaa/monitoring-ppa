<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Atasan = 'atasan';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::SuperAdmin->value => 'Super Admin',
            self::Admin->value => 'Admin',
            self::Atasan->value => 'Atasan',
        ];
    }
}
