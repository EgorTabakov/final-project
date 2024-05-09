<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case User = 'User';
    case Manager = 'Manager';
    case BranchManager = 'Branch manager';
    case SeniorManager = 'Senior manager';
    case Admin = 'Admin';
    case SuperAdmin = 'Super admin';

    public function label(): string
    {
        return match ($this) {
            static::User => __(static::User->value),
            static::Manager => __(static::Manager->value),
            static::BranchManager => __(static::BranchManager->value),
            static::SeniorManager => __(static::SeniorManager->value),
            static::Admin => __(static::Admin->value),
            static::SuperAdmin => __(static::SuperAdmin->value),
        };
    }
}
