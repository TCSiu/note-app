<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Web\BaseController;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProjectController extends BaseController
{
    public function create(Request $request){
        $project = Project::create(['name' => 'test', 'description' => 'test']);
        $user_id = Auth::id();
        $user = User::find($user_id);
        $user->projects()->attach($project);
        return $project;
    }

    public function view(Request $request, int $id = 0){
        $project = Project::find($id);
        $tasks = $project->tasks;
        return view('dashboard.project.view', compact('tasks'));
    }

    public function test(Request $request, int $id = 0){
        $project = Project::find($id);
        $task = Task::create(['name' => 'test task', 'description' => 'test task']);
        $project->tasks()->save($task);
        return $project->tasks;
    }
}
