<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Web\BaseController;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TaskController extends BaseController
{
    public function test(){
        $task = Task::create(['name' => 'test', 'description' => 'test']);
        $user_id = Auth::id();
        $user = User::find($user_id);
        $user->tasks()->attach($task);
        return $task;
    }
}
