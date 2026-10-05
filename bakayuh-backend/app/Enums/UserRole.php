<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case AdminKanwil = 'admin_kanwil';
    case OperatorSatker = 'operator_satker';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::AdminKanwil => 'Admin Kanwil',
            self::OperatorSatker => 'Operator Satker',
            self::Viewer => 'Viewer',
        };
    }

    public function canManageUsers(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function canVerify(): bool
    {
        return in_array($this, [self::SuperAdmin, self::AdminKanwil]);
    }

    public function canManageMaster(): bool
    {
        return in_array($this, [self::SuperAdmin, self::AdminKanwil]);
    }
}
