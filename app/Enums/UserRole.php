<?php

namespace App\Enums;

enum UserRole: string
{
    case PLANNER = 'planner';
    case MANAGER = 'manager';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match($this) {
            self::PLANNER => 'planner',
            self::MANAGER => 'manager',
            self::ADMIN => 'admin',
        };
    }
}
