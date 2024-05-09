<?php

namespace App\Enums;

enum PermissionEnum: string
{
    // Доступ к проектам
    case ProjectAuthor = 'Project author'; // Оперирует проектами, где он автор
    case ProjectCustomer = 'Project customer'; // Оперирует проектами, где он покупатель
    case ProjectManager = 'Project manager'; // Оперирует проектами, где он менеджер    
    case ProjectHisDepartment = 'Project his department'; // Оперирует проектами своего отдела
    case ProjectAll = 'All existing projects'; // Оперирует всеми существующими проектами

    public function label(): string
    {
        return match ($this) {
            static::ProjectAuthor => __(static::ProjectAuthor->value),
            static::ProjectCustomer => __(static::ProjectCustomer->value),
            static::ProjectManager => __(static::ProjectManager->value),
            static::ProjectHisDepartment => __(static::ProjectHisDepartment->value),
            static::ProjectAll => __(static::ProjectAll->value),
        };
    }
}
