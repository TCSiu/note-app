<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

Route::group(['middleware' => 'auth:api'], function(){
    Route::get('/test', [AuthController::class, 'test']);

    Route::group(['prefix' => 'project'], function(){
        Route::post('/create', [ProjectController::class, 'create']);
        Route::post('/assign', [ProjectController::class, 'assignProject']);
        Route::post('/deallocate', [ProjectController::class, 'deallocateProject']);
        Route::get('/view/{project_id}', [ProjectController::class, 'view']);
        Route::get('/edit/{project_id}', [ProjectController::class, 'edit']);
        Route::post('/store/{project_id}', [ProjectController::class, 'store']);
        Route::delete('/delete/{project_id}', [ProjectController::class, 'delete']);
    });

    Route::group(['prefix' => 'task'], function(){
        Route::post('/create', [TaskController::class, 'create']);
        Route::post('/edit/{task_id}', [TaskController::class, 'edit']);
        Route::post('/assign', [TaskController::class, 'assign']);
        Route::post('/deallocate', [TaskController::class, 'deallocate']);
        Route::post('/change-status/{task_id}', [TaskController::class, 'changeStatus']);
    });
});
