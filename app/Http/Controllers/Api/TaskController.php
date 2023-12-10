<?php

namespace App\Http\Controllers\Api;

use App\Enum\TaskStatusEnum;
use App\Http\Controllers\Api\BaseController;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class TaskController extends BaseController
{
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|integer|exists:projects,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|nullable',
        ]);
        if($validator->fails()){
            return $this->sendError('Create Task Fail', $validator->errors());
        }
        $validated = $validator->validated();
        $validated['status'] = TaskStatusEnum::WORKING;
        try{
            $project = Project::where(['id' => $validated['project_id'], 'is_deleted' => 0, 'is_active' => 1])->first();
            if(!isset($project)){
                return $this->sendError('Create Task Fail', ['Project is deleted']);
            }
            $task = Task::create($validated);
            $project->tasks()->save($task);
            return $this->sendResponse($project->tasks, 'Create Task Success');
        }catch(\Exception $e){
            return $this->sendError('Create Task Fail', $e->getMessage());
        }
    }

    public function edit(Request $request, $task_id = -1){
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            // 'project_id' => 'required|integer|exists:projects,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|nullable',
        ]);
        if($validator->fails()){
            return $this->sendError('Update Task Fail', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            $task = Task::where(['id' => $task_id, 'is_deleted' => 0])->first();
            if(!isset($task)){
                return $this->sendError('Update Task Fail', ['Task is deleted']);
            }
            $task->update($validated);
            return $this->sendResponse($task, 'Update Task Success');
        }catch(\Exception $e){
            return $this->sendError('Update Task Fail', $e->getMessage());
        }
    }

    public function assign(Request $request){
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|integer|exists:tasks,id',
            'user_id' => 'required|integer|exists:users,id'
        ]);
        if($validator->fails()){
            return $this->sendError('Update Task Fail', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            $task = Task::where(['id' => $validated['task_id'], 'is_deleted' => 0])->first();
            $user = User::where(['id' => $validated['user_id'], 'is_deleted' => 0])->first();
            $project = $task->projects;
            if(!isset($project)){
                return $this->sendError('Assign Task Fail', ['Project doesn\'t exist']);
            }else{
                if($project->is_deleted == 1){
                    return $this->sendError('Assign Task Fail', ['Project is deleted']);
                }
            }
            if(!isset($task)){
                return $this->sendError('Assign Task Fail', ['Task is deleted']);
            }
            if(!isset($user)){
                return $this->sendError('Assign Task Fail', ['User is deleted']);
            }
            if(!($project->owners->contains($user) || $project->editors->contains($user))){
                return $this->sendError('Assign Task Fail', ['You have no permission to assign the task']);
            }
            if($task->users->contains($user)){
                return $this->sendError('Assign Task Fail', ['Already assign to this user']);
            }
            $task->users()->attach($user);
            $task = Task::where(['id' => $validated['task_id'], 'is_deleted' => 0])->first();
            return $this->sendResponse($task, 'Assign Task Success');
        }catch(\Exception $e){
            return $this->sendError('Assign Task Fail', $e->getMessage());
        }
    }

    public function deallocate(Request $request){
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|integer|exists:tasks,id',
            'user_id' => 'required|integer|exists:users,id'
        ]);
        if($validator->fails()){
            return $this->sendError('Update Task Fail', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            $task = Task::where(['id' => $validated['task_id'], 'is_deleted' => 0])->first();
            $user = User::where(['id' => $validated['user_id'], 'is_deleted' => 0])->first();
            $project = $task->projects;
            if(!isset($project)){
                return $this->sendError('Deallocate Task Fail', ['Project doesn\'t exist']);
            }else{
                if($project->is_deleted == 1){
                    return $this->sendError('Deallocate Task Fail', ['Project is deleted']);
                }
            }
            if(!isset($task)){
                return $this->sendError('Deallocate Task Fail', ['Task is deleted']);
            }
            if(!isset($user)){
                return $this->sendError('Deallocate Task Fail', ['User is deleted']);
            }
            if(!($project->owners->contains($user) || $project->editors->contains($user))){
                return $this->sendError('Deallocate Task Fail', ['You have no permission to assign the task']);
            }
            if(!$task->users->contains($user)){
                return $this->sendError('Deallocate Task Fail', ['Already deallocate to this user']);
            }
            $task->users()->detach($user);
            $task = Task::where(['id' => $validated['task_id'], 'is_deleted' => 0])->first();
            return $this->sendResponse($task, 'Deallocate Task Success');
        }catch(\Exception $e){
            return $this->sendError('Deallocate Task Fail', $e->getMessage());
        }
    }

    public function changeStatus(Request $request, $task_id = -1){
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'status' => ['required', new Enum(TaskStatusEnum::class)],
        ]);
        if($validator->fails()){
            return $this->sendError('Create Task Fail', $validator->errors());
        }
        $validated = $validator->validated();
        $task = Task::where(['id' => $task_id, 'is_deleted' => 0])->first();
        if(!isset($task)){
            return $this->sendError('Deallocate Task Fail', ['Task is deleted']);
        }
        $status = TaskStatusEnum::tryFrom($validated['status']);
        if(!isset($status)){
            return $this->sendError('Change Task Status Fail', 'Unknown task status');
        }
        $task->status = $status;
        $task->save();
        return $this->sendResponse($task, 'Change task status success');
    }
}
