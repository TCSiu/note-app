<?php

namespace App\Http\Controllers\Api;

use App\Enum\TaskStatusEnum;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Task\AssignTaskRequest;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\DeallocateTaskRequest;
use App\Http\Requests\Task\DeleteTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\ViewTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class TaskController extends BaseController
{
    public function create(CreateTaskRequest $request){
        return $request->handle();
    }

    public function view(ViewTaskRequest $request){
        return $request->handle();
    }

    // public function edit(Request $request, $task_id = -1){
    //     $task = Project::where(['id' => $task_id])->first();
    //     $user = Auth::guard('api')->user();
    //     $project = $task->projects;
    //     if(!isset($project)){
    //         return $this->sendError('Edit Project Fail', ['Project is deleted or not exist']);
    //     }
    //     if(!($project->owners->contains($user) || $project->editors->contains($user))){
    //         return $this->sendError('Edit Project Fail', ['You have no permission to edit this project']);
    //     }
    //     return $this->sendResponse($project, 'Edit Project Success');
    // }

    public function update(UpdateTaskRequest $request){
        return $request->handle();
    }

    public function assign(AssignTaskRequest $request){
        return $request->handle();
    }

    public function deallocate(DeallocateTaskRequest $request){
        return $request->handle();
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
            return $this->sendError('Deallocate Task Fail', ['error' => 'Task is deleted or not exist']);
        }
        $status = TaskStatusEnum::tryFrom($validated['status']);
        if(!isset($status)){
            return $this->sendError('Change Task Status Fail', ['error' => 'Unknown task status']);
        }
        DB::beginTransaction();
        try{
            $task->status = $status;
            $task->save();
            DB::commit();
            return $this->sendResponse($task, 'Change task status success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Fail', $e->getMessage());
        }
    }

    public function delete(DeleteTaskRequest $request){
        return $request->handle();
    }
}
