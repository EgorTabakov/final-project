<?php

namespace App\Http\Controllers\ApiV1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Str;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Project;
use App\Enums\UserRoleEnum;
use App\Enums\PermissionEnum;
use App\Enums\ErrorEnum;

class ProjectJsonController extends BaseJsonController
{

    public function index(Request $request)
    {
        try {
            $projectData = $request->validate([
                'name' => 'nullable',
                'customer_email' => 'nullable|string|email',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date',
                'archive' => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendValidationError($e);
        }
        $user = Auth::user();
        // Поля, которые мы отдаем, пока отдаем все
        // config('app.visible_days') - возвращает количество дней, которые показывать по умолчанию (для всех функций) c даты последнего редактирования
        $fromDate = isset($projectData['from_date']) ? Carbon::parse($projectData['from_date']) : Carbon::now()->subDays(config('app.visible_days'));
        $to_date = isset($projectData['to_date']) ? Carbon::parse($projectData['to_date']) : null;

        // Выбираем таблицу и поля
        $projects = Project::select();
        // Сначала проверяем максимальные права, если они есть, то просто пропускаем этот блок
        if (!$user->can(PermissionEnum::ProjectAll->value)) {
            // Тут по очереди добавляем автора и менеджера или покупателя
            $projects->where(function (Builder $query) use ($user) {
                // Для роли User
                if ($user->can(PermissionEnum::ProjectAuthor->value) && $user->can(PermissionEnum::ProjectCustomer->value))
                    $query->where('author_id', $user->id)
                        ->orWhere('customer_id', $user->id);
                // Для роли Manager и старше
                if ($user->can(PermissionEnum::ProjectAuthor->value) && $user->can(PermissionEnum::ProjectManager->value))
                    $query->where('author_id', $user->id)
                        ->orWhere('manager_id', $user->id);
            });

        }
        // Проверяем даты
        $projects->where('updated_at', '>', $fromDate);
        if ($to_date)
            $projects->where('updated_at', '<', $to_date);
        // Проверяем имя проекта через regexp
        if (isset($projectData['name']))
            $projects->where('name', 'REGEXP', $projectData['name']);
        // Проверяем на архивно-удаленные проекты
        if (isset($projectData['archive']) && $projectData['archive'])
            $projects->withTrashed();

        // Самый сложный момент, если есть права Project his department (видит проекты своего отдела)
        if ($user->can(PermissionEnum::ProjectHisDepartment->value) && !$user->can(PermissionEnum::ProjectAll->value)) {
            // Формируем основную выборку проектов по департаменту
            $depProjects = Project::select("projects.*")
                ->join("users", "projects.manager_id", "=", "users.id")
                ->join("departments", "departments.id", "=", "users.department_id")
                ->where('department_id', $user->department_id)
                ->where('projects.updated_at', '>', $fromDate);
            if ($to_date)
                $depProjects->where('projects.updated_at', '<', $to_date);
            // Проверяем имя проекта через regexp
            if (isset($projectData['name']))
                $depProjects->where('projects.name', 'REGEXP', $projectData['name']);
            // Проверяем на архивно-удаленные проекты
            if (isset($projectData['archive']) && $projectData['archive'])
                $depProjects->withTrashed();
            // Проверяем на архивно-удаленные проекты для union, вручную, иначе глючит
            if (!(isset($projectData['archive']) && $projectData['archive']))
                $projects->where('projects.deleted_at', NULL);
            $depProjects->union($projects->getQuery());
            $projects = $depProjects;
        }
        // Все, забираем сборную солянку :)
        $projects = $projects->latest('updated_at')->get();

        $count = count($projects);
        if (!$count)
            return $this->sendError(ErrorEnum::E003_PROJECTS_NOT_FOUND, 404); // Ошибка, проектов нет
        // Скрываем уникальные ключи, оставляем информацию о заблокированных проектах
        foreach ($projects as $project)
            $project->locked = $project->locked == null ? false : true;
        // Если проект 1 - возвращаем результат ввиде объекта, а не массива
        if ($count == 1)
            $projects = $projects[0];
        return $this->sendResponse(__("Projects retrieved successfully."), $projects);
    }

    public function department(Request $request)
    {
        $user = Auth::user();
        // Поля, которые мы отдаем, пока отдаем все
        // config('app.visible_days') - возвращает количество дней, которые показывать по умолчанию (для всех функций) c даты последнего редактирования
        $fromDate = Carbon::now()->subDays(config('app.visible_days'));

        // Должны быть права Project his department (видит проекты своего отдела)
        if (!$user->can(PermissionEnum::ProjectHisDepartment->value)) {
            return $this->sendError(ErrorEnum::E007_NER_SEE_DEP_PROJECTS, 403);
        }

        // Формируем основную выборку проектов по департаменту
        $projects = Project::select("projects.*")
            ->join("users", "projects.manager_id", "=", "users.id")
            ->join("departments", "departments.id", "=", "users.department_id")
            ->where('department_id', $user->department_id)
            ->where('projects.updated_at', '>', $fromDate);

        // Все, забираем
        $projects = $projects->latest('updated_at')->get();

        $count = count($projects);
        if (!$count)
            return $this->sendError(ErrorEnum::E003_PROJECTS_NOT_FOUND, 404); // Ошибка, проектов нет
        // Скрываем уникальные ключи, оставляем информацию о заблокированных проектах
        foreach ($projects as $project)
            $project->locked = $project->locked == null ? false : true;
        // Если проект 1 - возвращаем результат ввиде объекта, а не массива
        if ($count == 1)
            $projects = $projects[0];
        return $this->sendResponse(__("Projects retrieved successfully."), $projects);

    }

    public function user(Request $request)
    {
        $user = Auth::user();
        // Поля, которые мы отдаем, пока отдаем все
        // config('app.visible_days') - возвращает количество дней, которые показывать по умолчанию (для всех функций) c даты последнего редактирования
        $fromDate = Carbon::now()->subDays(config('app.visible_days'));

        // Выбираем таблицу и поля
        $projects = Project::select();

        // Тут по очереди добавляем автора и менеджера или покупателя
        $projects->where(function (Builder $query) use ($user) {
            // Для роли User
            if ($user->can(PermissionEnum::ProjectAuthor->value) && $user->can(PermissionEnum::ProjectCustomer->value))
                $query->where('author_id', $user->id)
                    ->orWhere('customer_id', $user->id);
            // Для роли Manager и старше
            if ($user->can(PermissionEnum::ProjectAuthor->value) && $user->can(PermissionEnum::ProjectManager->value))
                $query->where('author_id', $user->id)
                    ->orWhere('manager_id', $user->id);
        });

        // Проверяем даты
        $projects->where('updated_at', '>', $fromDate);

        // Все, забираем
        $projects = $projects->latest('updated_at')->get();

        $count = count($projects);
        if (!$count)
            return $this->sendError(ErrorEnum::E003_PROJECTS_NOT_FOUND, 404); // Ошибка, проектов нет
        // Скрываем уникальные ключи, оставляем информацию о заблокированных проектах
        foreach ($projects as $project)
            $project->locked = $project->locked == null ? false : true;
        // Если проект 1 - возвращаем результат ввиде объекта, а не массива
        if ($count == 1)
            $projects = $projects[0];
        return $this->sendResponse(__("Projects retrieved successfully."), $projects);

    }

    public function store(Request $request)
    {
        try {
            $projectData = $request->validate([
                'name' => 'nullable|string',
                'customer_email' => 'nullable|string|email|exists:users,email',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendValidationError($e);
        }

        $user = Auth::user();
        $projectData['author_id'] = $user->id;
        // Если пользователь покупатель (роль User) то у нас 1 сценарий
        if (UserRoleEnum::User->value == $user->getRoleNames()[0]) {
            $projectData['customer_id'] = $user->id;
        } else { // Пользователь не покупатель (роль выше User)
            // Проверяем, есть ли у проекта покупатель
            if (isset($projectData['customer_email'])) {
                $customer = User::where('email', $projectData['customer_email'])->first();

                // Проверяем, что покупатель не продавец (роль User) в целях безопасности
                if (UserRoleEnum::User->value != $customer->getRoleNames()[0]) {
                    return $this->sendError(ErrorEnum::E004_AUTHORIZATION_LOGIC_BROKEN, 403);
                }
                unset($projectData['customer_email']);
                $projectData['customer_id'] = $customer->id;
            }
            $projectData['manager_id'] = $user->id;
        }
        $project = Project::create($projectData);
        $answer['id'] = $project->id;
        return $this->sendResponse(__("Project created successfully."), $answer);
    }

    public function locking(Request $request, string $project_id)
    {
        if (!ctype_digit($project_id))
            return $this->sendError(ErrorEnum::E010_REQUEST_PARSING_ERROR, 403);
        try {
            $projectData = $request->validate([
                'lock' => 'required|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendValidationError($e);
        }
        $user = Auth::user();
        // Если проект нужно залочить под редактирование
        if ($user->last_project_id == $project_id || $this->userCanUseProject($user, $project_id, false, true)) {
            if ($projectData['lock']) {
                $user->last_project_id = $project_id;
                $user->save();
                /** @var \App\Models\Project $lastProject **/
                $lastProject = $user->last_project()->withTrashed()->first();
                // Проверяем, не залочен ли проект
                if ($lastProject->locked != null) {
                    return $this->sendError(ErrorEnum::E005_FILE_LOCKED, 409);
                }
                $lastProject->locked = random_int(PHP_INT_MIN, PHP_INT_MAX);
                if ($lastProject->trashed())
                    $lastProject->restore();
                $lastProject->save();
                $answer['keylock'] = $lastProject->locked;
                return $this->sendResponse(__("The project is locked for editing."), $answer);
            } else { // Значит проект нужно разлочить
                /** @var \App\Models\Project $lastProject **/
                $lastProject = $user->last_project()->withTrashed()->first();
                // Анлок - просто устанавливаем значение лока в null
                $lastProject->locked = null;
                if ($lastProject->trashed())
                    $lastProject->restore();
                $lastProject->save();
                return $this->sendResponse(__("The project is unlocked for modification."));
            }
        } else { // У пользователя недостаточно прав
            return $this->sendError(ErrorEnum::E006_NER_OR_FNF_TO_LOCK, 403);
        }
    }

    public function delete(Request $request, string $project_id)
    {
        if (!ctype_digit($project_id))
            return $this->sendError(ErrorEnum::E010_REQUEST_PARSING_ERROR, 403);
        $user = Auth::user();
        // Если проект нужно залочить под редактирование
        if ($user->last_project_id == $project_id || $this->userCanUseProject($user, $project_id, false)) {
            /** @var \App\Models\Project $lastProject **/
            $lastProject = $user->last_project()->first();
            // Проверяем, не залочен ли проект
            if ($lastProject->locked != null) {
                return $this->sendError(ErrorEnum::E005_FILE_LOCKED, 409);
            }
            $lastProject->delete();
            return $this->sendResponse(__("The project archived successfully."));
        } else { // У пользователя недостаточно прав
            return $this->sendError(ErrorEnum::E006_NER_OR_FNF_TO_LOCK, 403);
        }
    }
}
