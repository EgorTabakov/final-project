<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Apiv1\UserJsonController;
use App\Http\Controllers\Apiv1\ProjectJsonController;
use App\Http\Controllers\Apiv1\CommandJsonController;
use App\Http\Controllers\Apiv1\StatusController;

// Проверка работы сервера
Route::get('/status', [StatusController::class, 'serverStatus']);

// регистрация пользователя
Route::post('/user/register', [UserJsonController::class, 'register']);

// вход в систему по логину/паролю и получение токена
Route::post('/user/login', [UserJsonController::class, 'login']);

// Информация о пользователе
Route::get('/user', [UserJsonController::class, 'userInfo'])->middleware('auth:sanctum');
Route::get('/user/{user_id}', [UserJsonController::class, 'userInfo'])->middleware('auth:sanctum');

// Работа с проектом
Route::get('/project', [ProjectJsonController::class, 'index'])->middleware('auth:sanctum'); // Вывод всех доступных проектов пользователю (с параметрами)
Route::get('/project/user', [ProjectJsonController::class, 'user'])->middleware('auth:sanctum'); // Вывод проектов пользователя где он менеджер или покупатель
Route::get('/project/department', [ProjectJsonController::class, 'department'])->middleware('auth:sanctum'); // Вывод проектов пользователя в его отделе, при условии, что он Branch Manager или старше
Route::post('/project', [ProjectJsonController::class, 'store'])->middleware('auth:sanctum'); // Создание проекта
Route::post('/project/{project_id}/locking', [ProjectJsonController::class, 'locking'])->middleware('auth:sanctum'); // Блокировка проекта
Route::delete('/project/{project_id}', [ProjectJsonController::class, 'delete'])->middleware('auth:sanctum'); // Удаление проекта

// Работа с файлом проекта
Route::get('/project/{project_id}/command/last', [CommandJsonController::class, 'fileLastCommand'])->middleware('auth:sanctum'); // Чтение последней строки
Route::get('/project/{project_id}/command/{line_id?}', [CommandJsonController::class, 'fileOut'])->middleware('auth:sanctum'); // Чтение всего проекта или одной строки
Route::post('/project/{project_id}/command', [CommandJsonController::class, 'fileAddTopCommand'])->middleware('auth:sanctum'); // Запись последней строки
Route::delete('/project/{project_id}/command', [CommandJsonController::class, 'fileDeleteTopCommand'])->middleware('auth:sanctum'); // Запись последней строки




