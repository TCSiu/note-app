<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends BaseController
{
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|nullable',
        ]);
        if($validator->fails()){
            return $this->sendError('Create Project Fail', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            $project = Project::create($validated);
            return $this->sendResponse($project, 'Create Project Success');
        }catch(\Exception $e){
            return $this->sendError('Create Project Fail', $e->getMessage());
        }
    }

    public function addTask(Request $request){
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|integer|exists:projects,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|nullable',
        ]);
        if($validator->fails()){
            return $this->sendError('Create Task Fail', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            $project = Project::find($validated['project_id']);
            $task = Task::create($validated);
            $project->tasks()->save($task);
            return $this->sendResponse($project->tasks, 'Create Task Success');
        }catch(\Exception $e){
            return $this->sendError('Create Task Fail', $e->getMessage());
        }
    }

    public function assignProject(Request $request){
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|integer|exists:projects,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to assign project', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            $project = Project::find($validated['project_id']);
            $user = User::find($validated['user_id']);
            $project->users()->attach($user);
        }
    }
}
