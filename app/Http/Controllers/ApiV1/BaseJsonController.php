<?php

namespace App\Http\Controllers\ApiV1;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

use App\Models\Project;
use App\Enums\PermissionEnum;
use App\Enums\ErrorEnum;

abstract class BaseJsonController
{
    protected function sendResponse($message, $result = '', $success = true, $code = 200): JsonResponse
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];
        if ($result)
            $response['data'] = $result;
        return response()->json($response, $code);
    }

    protected function sendError(ErrorEnum $error, $code)
    {
        $answer['error_id'] = $error;
        $answer['error_message'] = $error->label();
        return $this->sendResponse($error->label(), $answer, false, $code);
    }

    protected function sendValidationError($e)
    {
        // Вычитываем типы ошибок
        foreach ($e->validator->failed() as $field => $error) {
            $answer[] = array(
                'error_id' => ErrorEnum::E001_VALIDATION_ERROR,
                'error_field' => $field,
                'error_type' => array_keys($error)[0],
            );
        }
        // Добавляем к ним сообщения
        $messages = $e->validator->errors()->all();
        foreach ($messages as $key => $error)
            $answer[$key]['error_message'] = $error;

        // Формируем сообщение об ошибке на основе ошибок если их 2 или больше
        $message = $messages[0];
        $count = count($messages) - 1;
        if ($count) {
            $pluralized = $count === 1 ? 'error' : 'errors';
            $message .= ' ' . $e->validator->getTranslator()->choice("(and :count more $pluralized)", $count, compact('count'));
        }

        // Если ошибка одна
        if (!$count)
            $answer = $answer[0];
        return $this->sendResponse($message, $answer, false, 422);
    }
    protected function userCanUseProject($user, $projectId, bool $setLast = true, $deleted = false): bool
    {
        // Собираем проект, как в выдаче проекта, но без лишних полей
        // и добавляем проверку на id проекта
        $projects = Project::select()->where("id", $projectId);
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
        if ($deleted)
            $projects->withTrashed();

        // Самый сложный момент, если есть права Project his department (видит проекты своего отдела)
        if ($user->can(PermissionEnum::ProjectHisDepartment->value) && !$user->can(PermissionEnum::ProjectAll->value)) {
            // Формируем основную выборку проектов по департаменту
            $depProjects = Project::select("projects.*")
                ->join("users", "projects.manager_id", "=", "users.id")
                ->join("departments", "departments.id", "=", "users.department_id")
                ->where('department_id', $user->department_id)
                ->where("projects.id", $projectId);
            // Проверяем на архивно-удаленные проекты
            if ($deleted)
                $depProjects->withTrashed();
            // Проверяем на архивно-удаленные проекты для union, вручную, иначе глючит
            if (!$deleted)
                $projects->where('projects.deleted_at', NULL);
            $depProjects->union($projects->getQuery());
            $projects = $depProjects;
        }
        // Проверяем наличие хоть одной записи
        $lastProject = $projects->first();
        if ($lastProject) {
            if ($setLast) {
                $user->last_project_id = $lastProject->id;
                $user->save();
            }
            return true;
        } else
            return false;
    }
}
