<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use App\Models\User;
use App\Models\Department;
use App\Enums\UserRoleEnum;
use App\Enums\PermissionEnum;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $headquarters = Department::create([
            'name' => 'Разработчики и руководство компании Titan GS',
        ]);

        // Создаем все права
        foreach (PermissionEnum::cases() as $permission)
            Permission::findOrCreate($permission->value);

        // Права пользователя User
        $role = Role::findOrCreate(UserRoleEnum::User->value);
        $role->givePermissionTo([
            PermissionEnum::ProjectAuthor->value,
            PermissionEnum::ProjectCustomer->value,
        ]);

        // Права пользователя Manager
        $role = Role::findOrCreate(UserRoleEnum::Manager->value);
        $role->givePermissionTo([
            PermissionEnum::ProjectAuthor->value,
            PermissionEnum::ProjectManager->value,
        ]);

        // Права пользователя Branch Manager
        $role = Role::findOrCreate(UserRoleEnum::BranchManager->value);
        $role->givePermissionTo([
            PermissionEnum::ProjectAuthor->value,
            PermissionEnum::ProjectManager->value,
            PermissionEnum::ProjectHisDepartment->value,
        ]);

        // Права пользователя Branch Manager
        $role = Role::findOrCreate(UserRoleEnum::SeniorManager->value);
        $role->givePermissionTo([
            PermissionEnum::ProjectAuthor->value,
            PermissionEnum::ProjectManager->value,
            PermissionEnum::ProjectHisDepartment->value,
            PermissionEnum::ProjectAll->value,
        ]);

        // Права пользователя Admin
        $role = Role::findOrCreate(UserRoleEnum::Admin->value);
        $role->givePermissionTo([
            PermissionEnum::ProjectAuthor->value,
            PermissionEnum::ProjectManager->value,
            PermissionEnum::ProjectHisDepartment->value,
            PermissionEnum::ProjectAll->value,
        ]);

        // Суперпользователь, у такого пользователя права не проверяются
        // По идее все ответы должны быть true
        $superAdmin = User::create([
            'email' => 'titangssuperuser@r107.ru',
            'password' => Hash::make('123qweASD'),
            'department_id' => $headquarters->id
        ]);

        $role = Role::findOrCreate(UserRoleEnum::SuperAdmin->value);
        $superAdmin->assignRole($role);
    }
}
