<?php

namespace App\Enums;

enum UserRole: string
{
    case PLANNER = 'planner';
    case MANAGER = 'manager';
    case ADMIN = 'admin';
    case POLICY_MAKER = 'policy_maker';

    public function label(): string
    {
        return match($this) {
            self::PLANNER => 'planner',
            self::MANAGER => 'manager',
            self::ADMIN => 'admin',
            self::POLICY_MAKER => 'policy_maker'
        };
    }
}
