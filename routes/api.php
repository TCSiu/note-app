<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\WorkflowController;
use App\Models\File;
use App\Models\Project;
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

Route::get('/', function() {
    $path = File::find(6)->path;
    return url('storage/'.$path);
});

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::get('/token-refresh', [AuthController::class, 'getRefreshToken']);
Route::get('/token-check', [AuthController::class, 'checkToken']);

Route::group(['middleware' => 'auth:api'], function(){
    Route::get('/test', [AuthController::class, 'test']);
    Route::post('/user-validate', [AuthController::class, 'validateUser']);
    Route::get('/recommend-workflow', [WorkflowController::class, 'getSuggestedWorkflow']);

    Route::group(['prefix' => 'project'], function(){
        Route::get('/', [ProjectController::class, 'projects']);
        Route::post('/', [ProjectController::class, 'create']);
        Route::put('/{project_uuid}/allocation', [ProjectController::class, 'assign']);
        // Route::post('/{project_id}/assign-new', [ProjectController::class, 'assign_new']);
        Route::put('/{project_uuid}/deallocation', [ProjectController::class, 'deallocate']);
        Route::get('/{project_id}', [ProjectController::class, 'view']);
        Route::get('/{project_id}/edit', [ProjectController::class, 'edit']);
        Route::put('/{project_id}', [ProjectController::class, 'update']);
        Route::delete('/{project_id}', [ProjectController::class, 'delete']);
        Route::get('/{project_id}/restoration', [ProjectController::class, 'restore']);
        Route::get('/{project_uuid}/tasks', [ProjectController::class, 'tasks']);
        Route::get('/{project_uuid}/recommend-user', [ProjectController::class, 'getSuggestUser']);
        Route::delete('/{project_id}/workflow', [ProjectController::class, 'deleteWorkflow']);
    });

    Route::group(['prefix' => 'task'], function(){
        Route::post('/', [TaskController::class, 'create']);
        Route::get('/{task_id}', [TaskController::class, 'view']);
        // Route::get('/{task_id}/edit', [TaskController::class, 'edit']);
        Route::put('/{task_id}', [TaskController::class, 'update']);
        Route::post('/{task_id}/allocation', [TaskController::class, 'assign']);
        Route::post('/{task_id}/deallocation', [TaskController::class, 'deallocate']);
        Route::post('/{task_id}/change-status', [TaskController::class, 'changeStatus']);
    });

    Route::group(['prefix' => 'comment'], function(){
        Route::post('/create', [CommentController::class, 'create']);
        Route::put('/{comment_id}', [CommentController::class, 'update']);
        Route::delete('/{comment_id}', [CommentController::class, 'delete']);
    });

    Route::group(['prefix' => 'image'], function(){
        Route::post('/upload', [FileController::class, 'upload']);
    });
});
