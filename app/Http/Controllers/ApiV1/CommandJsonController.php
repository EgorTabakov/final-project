<?php

namespace App\Http\Controllers\ApiV1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Command;
use App\Enums\ErrorEnum;

class CommandJsonController extends BaseJsonController
{

    public function fileLastCommand(Request $request, string $project_id)
    {
        return $this->fileOut($request, $project_id, null, true);
    }

    public function fileOut(Request $request, string $project_id, ?string $line_id = '', bool $last_command = false)
    {
        if ((!ctype_digit($project_id)) || ($line_id && !ctype_digit($line_id)))
            return $this->sendError(ErrorEnum::E010_REQUEST_PARSING_ERROR, 403);
        $user = Auth::user();
        if ($user->last_project_id == $project_id || $this->userCanUseProject($user, $project_id)) {
            // Магия, чтобы получить не Id стоки, а номер строки в выдаче
            $rawQuery = 'select * FROM (SELECT ROW_NUMBER() OVER (ORDER BY id) as row_num, id, command, json, created_at, updated_at from commands WHERE project_id=' . $project_id . ') with_rows';
            if ($line_id)
                $rawQuery = $rawQuery . ' WHERE row_num =' . $line_id;
            if ($last_command)
                $rawQuery = $rawQuery . ' ORDER BY row_num DESC LIMIT 1';

            $userData = DB::select($rawQuery);
            $file = Command::hydrate($userData);

            if (!$file->count()) {
                return $this->sendError(ErrorEnum::E008_PROJECT_EMPTY_OR_NO_LINE, 404);
            }

            if ($file->count() == 1)
                $file = $file[0];

            return $this->sendResponse(__("File or file line returned."), $file);
        } else {
            return $this->sendError(ErrorEnum::E009_NER_OPER_PROJ_OR_NOT_EXIST, 403);
        }
    }

    public function fileAddTopCommand(Request $request, string $project_id)
    {
        if (!ctype_digit($project_id))
            return $this->sendError(ErrorEnum::E010_REQUEST_PARSING_ERROR, 403);
        try {
            $commandData = $request->validate([
                'keylock' => 'required|numeric',
                'command' => 'required|numeric|gt:0|max:65535',
                'json' => 'required|json',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendValidationError($e);
        }
        $user = Auth::user();
        //  Проект нужно выбрать для добавления строки
        if ($user->last_project_id == $project_id || $this->userCanUseProject($user, $project_id)) {
            $lastProject = $user->last_project()->first();
            if ($lastProject->locked != $commandData['keylock'])
                return $this->sendError(ErrorEnum::E011_KEY_NOT_VALID, 403);
            $commandData['project_id'] = $project_id;
            unset($commandData['keylock']);
            $command = Command::create($commandData);
            $answer['id'] = $command->id;
            return $this->sendResponse(__("The last command was successfully added to the file."), $answer);
        } else { // У пользователя недостаточно прав
            return $this->sendError(ErrorEnum::E009_NER_OPER_PROJ_OR_NOT_EXIST, 403);
        }
    }

    public function fileDeleteTopCommand(Request $request, string $project_id)
    {
        if (!ctype_digit($project_id))
            return $this->sendError(ErrorEnum::E010_REQUEST_PARSING_ERROR, 403);
        try {
            $commandData = $request->validate([
                'keylock' => 'required|numeric',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendValidationError($e);
        }
        $user = Auth::user();
        //  Проект нужно выбрать для добавления строки
        if ($user->last_project_id == $project_id || $this->userCanUseProject($user, $project_id)) {
            $lastProject = $user->last_project()->first();
            if ($lastProject->locked != $commandData['keylock'])
                return $this->sendError(ErrorEnum::E011_KEY_NOT_VALID, 403);
            $command = Command::select()->where('project_id', $project_id)->orderBy('id', 'desc')->first();
            if (!$command)
                return $this->sendError(ErrorEnum::E008_PROJECT_EMPTY_OR_NO_LINE, 404);
            $command->delete();
            return $this->sendResponse(__("The last command was successfully deleted from the file."));
        } else { // У пользователя недостаточно прав
            return $this->sendError(ErrorEnum::E009_NER_OPER_PROJ_OR_NOT_EXIST, 403);
        }
    }
}
