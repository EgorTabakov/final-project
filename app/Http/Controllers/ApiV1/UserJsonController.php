<?php

namespace App\Http\Controllers\ApiV1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Enums\UserRoleEnum;
use App\Enums\ErrorEnum;


class UserJsonController extends BaseJsonController
{
    public function userInfo(Request $request, string $id = "")
    {
        $user = $id == "" ? Auth::user() : User::find($id);
        if ($user == null) {
            $answer['error'] = __("User not found.");
            return $this->sendResponse(__("User not found."), $answer, false, 404);
        }
        $answer['user_id'] = $user->id;
        return $this->sendResponse(__("User information."), $answer);
    }

    public function login(Request $request)
    {
        try {
            $userData = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|min:8',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendValidationError($e);
        }

        if (Auth::attempt($userData)) {
            /** @var \App\Models\User $user **/
            $user = Auth::user();
            $user->tokens()->delete();
            $answer['token'] = $user->createToken('MyApp')->plainTextToken;
            return $this->sendResponse(__("The user has been successfully authorized."), $answer);
        }

        $user = User::where('email', $userData['email'])->first();
        if ($user === null)
            return $this->sendError(ErrorEnum::E002_AUTHORIZATION_ERROR, 401);
        else
            return $this->sendError(ErrorEnum::E012_PASS_NOT_VALID_OR_USER_BLOCKED, 401);
    }

    public function register(Request $request)
    {
        try {
            $userData = $request->validate([
                'email' => 'required|string|email|unique:users',
                'password' => 'required|min:8',
                'role' => ['required', Rule::in([UserRoleEnum::User->value, UserRoleEnum::Manager->value])],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendValidationError($e);
        }

        $userData['password'] = Hash::make($userData['password']);
        $role = $userData['role'];
        unset($userData['role']);
        $user = User::create($userData);
        $user->assignRole($role);
        return $this->sendResponse(__("The user registered successfully."));
    }
}
