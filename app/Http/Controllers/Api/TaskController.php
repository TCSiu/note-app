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
            $project = Project::where(['id' => $validated['project_id']])->first();
            if(!isset($project)){
                return $this->sendError('Create Task Fail', ['Project is deleted or not exist']);
            }
            $task = Task::create($validated);
            $project->tasks()->save($task);
            return $this->sendResponse($project->tasks, 'Create Task Success');
        }catch(\Exception $e){
            return $this->sendError('Create Task Fail', $e->getMessage());
        }
    }

    public function view(Request $requset, $task_id = -1){
        $task = Project::where(['id' => $task_id])->first();
        $user = Auth::guard('api')->user();
        if(isset($task)){
            if(!$task->users->contains($user)){
                return $this->sendError('View Task Fail', ['User doesn\'t have permission to view this task']);
            }
            return $this->sendResponse($task, 'View Task Success');
        }
        return $this->sendError('Create Task Fail', ['Task is deleted']);
    }

    public function edit(Request $request, $task_id = -1){
        $task = Project::where(['id' => $task_id])->first();
        $user = Auth::guard('api')->user();
        $project = $task->projects;
        if(!isset($project)){
            return $this->sendError('Edit Project Fail', ['Project is deleted or not exist']);
        }
        if(!($project->owners->contains($user) || $project->editors->contains($user))){
            return $this->sendError('Edit Project Fail', ['You have no permission to edit this project']);
        }
        return $this->sendResponse($project, 'Edit Project Success');
    }

    public function update(Request $request, $task_id = -1){
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
            $task = Task::where(['id' => $task_id])->first();
            if(!isset($task)){
                return $this->sendError('Update Task Fail', ['Task is deleted or not exist']);
            }
            $task->update($validated);
            return $this->sendResponse($task, 'Update Task Success');
        }catch(\Exception $e){
            return $this->sendError('Update Task Fail', $e->getMessage());
        }
    }

    public function assign(Request $request, int $task_id = -1){
        $validator = Validator::make($request->all(), [
            // 'task_id' => 'required|integer|exists:tasks,id',
            'user_id' => 'required|integer|exists:users,id'
        ]);
        if($validator->fails()){
            return $this->sendError('Update Task Fail', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            // $task = Task::where(['id' => $validated['task_id']])->first();
            $task = Task::where(['id' => $task_id])->first();
            $user = User::where(['id' => $validated['user_id']])->first();
            $project = $task->projects;
            if(!isset($project)){
                return $this->sendError('Assign Task Fail', ['Project doesn\'t exist']);
            }else{
                if(isset($project->deleted_at)){
                    return $this->sendError('Assign Task Fail', ['Project is deleted']);
                }
            }
            if(!isset($task)){
                return $this->sendError('Assign Task Fail', ['Task is deleted or not exist']);
            }
            if(!isset($user)){
                return $this->sendError('Assign Task Fail', ['User is deleted or not exist']);
            }
            if(!($project->owners->contains($user) || $project->editors->contains($user))){
                return $this->sendError('Assign Task Fail', ['You have no permission to assign the task']);
            }
            if($task->users->contains($user)){
                return $this->sendError('Assign Task Fail', ['Already assign to this user']);
            }
            $task->users()->attach($user);
            $task = Task::where(['id' => $task_id])->first();
            return $this->sendResponse($task, 'Assign Task Success');
        }catch(\Exception $e){
            return $this->sendError('Assign Task Fail', $e->getMessage());
        }
    }

    public function deallocate(Request $request, int $task_id = -1){
        $validator = Validator::make($request->all(), [
            // 'task_id' => 'required|integer|exists:tasks,id',
            'user_id' => 'required|integer|exists:users,id'
        ]);
        if($validator->fails()){
            return $this->sendError('Update Task Fail', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            // $task = Task::where(['id' => $validated['task_id']])->first();
            $task = Task::where(['id' => $task_id])->first();
            $user = User::where(['id' => $validated['user_id']])->first();
            $project = $task->projects;
            if(!isset($project)){
                return $this->sendError('Deallocate Task Fail', ['Project doesn\'t exist']);
            }else{
                if(isset($project->deleted_at)){
                    return $this->sendError('Deallocate Task Fail', ['Project is deleted or not exist']);
                }
            }
            if(!isset($task)){
                return $this->sendError('Deallocate Task Fail', ['Task is deleted or not exist']);
            }
            if(!isset($user)){
                return $this->sendError('Deallocate Task Fail', ['User is deleted or not exist']);
            }
            if(!($project->owners->contains($user) || $project->editors->contains($user))){
                return $this->sendError('Deallocate Task Fail', ['You have no permission to assign the task']);
            }
            if(!$task->users->contains($user)){
                return $this->sendError('Deallocate Task Fail', ['Already deallocate to this user']);
            }
            $task->users()->detach($user);
            $task = Task::where(['id' => $task_id])->first();
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
        $task = Task::where(['id' => $task_id])->first();
        if(!isset($task)){
            return $this->sendError('Deallocate Task Fail', ['Task is deleted or not exist']);
        }
        $status = TaskStatusEnum::tryFrom($validated['status']);
        if(!isset($status)){
            return $this->sendError('Change Task Status Fail', 'Unknown task status');
        }
        $task->status = $status;
        $task->save();
        return $this->sendResponse($task, 'Change task status success');
    }

    public function delete(Request $request, int $task_id = -1){
        $user = Auth::guard('api')->user();
        $task = Project::where(['id' => $task_id])->first();
        $project = $task->projects;
        if(!isset($task)){
            return $this->sendError('Delete Task Fail', ['Task is deleted or not exist']);
        }
        if(!$project->owners->contains($user) && !$project->editors->contains($user)){
            return $this->sendError('Delete Task Fail', ['You have no permission']);
        }
        $task->delete();
        $task->save();
        return $this->sendResponse($task, 'Delete Task Success');
    }
}
