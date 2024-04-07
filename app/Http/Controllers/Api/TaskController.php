<?php

namespace App\Http\Controllers\Api;

use App\Enum\TaskStatusEnum;
use App\Http\Controllers\Api\BaseController;
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
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|integer|exists:projects,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|nullable',
            'workflow_uuid' => 'required|uuid',
        ]);
        if($validator->fails()){
            return $this->sendError('Create Task Fail', $validator->errors());
        }
        $validated = $validator->validated();
        $project = Project::where(['id' => $validated['project_id']])->first();
        $workflow = json_decode($project->workflow, true);
        if(!isset($project)){
            return $this->sendError('Create Task Fail', ['error' => 'Please select an existing project'], 400);
        }
        if (!in_array($validated['workflow_uuid'], array_keys($workflow))) {
            return $this->sendError('Create Task Fail', ['error' => 'Please select an existing project workflow'], 400);
        }
        DB::beginTransaction();
        try{
            $task = Task::create($validated);
            $project->tasks()->save($task);
            DB::commit();
            $task->refresh();
            return $this->sendResponse($task, 'Create Task Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Create Task Fail', $e->getMessage());
        }
    }

    public function view(Request $requset, $task_id = -1){
        $task = Task::where(['id' => $task_id])->with('attachments')->first();
        $user = Auth::guard('api')->user();
        if(isset($task)){
            // if(!$task->users->contains($user)){
            //     return $this->sendError('View Task Fail', ['User doesn\'t have permission to view this task']);
            // }
            return $this->sendResponse($task, 'View Task Success');
        }
        return $this->sendError('View Task Fail', ['Task is deleted']);
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

    public function update(Request $request, $task_id = -1){
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            // 'project_id' => 'required|integer|exists:projects,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|nullable',
            'workflow_uuid' => 'required|uuid'
        ]);
        if($validator->fails()){
            return $this->sendError('Update Task Fail', $validator->errors());
        }
        $validated = $validator->validated();
        $task = Task::where(['id' => $task_id])->first();
        $project = $task->project;
        $workflow = json_decode($project->workflow, true);
        if(!isset($task)){
            return $this->sendError('Update Task Fail', ['error' => 'Task is deleted or not exist']);
        }
        if (!in_array($validated['workflow_uuid'], $workflow)) {
            return $this->sendError('Update Task Fail', ['error' => 'Please select an existing project workflow']);
        }
        DB::beginTransaction();
        try{
            $task->update($validated);
            DB::commit();
            return $this->sendResponse($task, 'Update Task Success');
        }catch(\Exception $e){
            DB::rollBack();
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
        $task = Task::where(['id' => $task_id])->first();
        $user = User::where(['id' => $validated['user_id']])->first();
        $project = $task->projects;
        if(!isset($project)){
            return $this->sendError('Assign Task Fail', ['error' => 'Project doesn\'t exist']);
        }else{
            if(isset($project->deleted_at)){
                return $this->sendError('Assign Task Fail', ['error' => 'Project is deleted']);
            }
        }
        if(!isset($task)){
            return $this->sendError('Assign Task Fail', ['error' => 'Task is deleted or not exist']);
        }
        if(!isset($user)){
            return $this->sendError('Assign Task Fail', ['error' => 'User is deleted or not exist']);
        }
        if(!($project->owners->contains($user) || $project->editors->contains($user))){
            return $this->sendError('Assign Task Fail', ['error' => 'You have no permission to assign the task']);
        }
        if($task->users->contains($user)){
            return $this->sendError('Assign Task Fail', ['error' => 'Already assign to this user']);
        }
        DB::beginTransaction();
        try{
            // $task = Task::where(['id' => $validated['task_id']])->first();
            $task->users()->attach($user);
            $task->refresh();
            // $task = Task::where(['id' => $task_id])->first();
            DB::commit();
            return $this->sendResponse($task, 'Assign Task Success');
        }catch(\Exception $e){
            DB::rollBack();
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
        $task = Task::where(['id' => $task_id])->first();
        $user = User::where(['id' => $validated['user_id']])->first();
        $project = $task->projects;
        if(!isset($project)){
            return $this->sendError('Deallocate Task Fail', ['error' => 'Project doesn\'t exist']);
        }else{
            if(isset($project->deleted_at)){
                return $this->sendError('Deallocate Task Fail', ['error' => 'Project is deleted or not exist']);
            }
        }
        if(!isset($task)){
            return $this->sendError('Deallocate Task Fail', ['error' => 'Task is deleted or not exist']);
        }
        if(!isset($user)){
            return $this->sendError('Deallocate Task Fail', ['error' => 'User is deleted or not exist']);
        }
        if(!($project->owners->contains($user) || $project->editors->contains($user))){
            return $this->sendError('Deallocate Task Fail', ['error' => 'You have no permission to assign the task']);
        }
        if(!$task->users->contains($user)){
            return $this->sendError('Deallocate Task Fail', ['error' => 'Already deallocate to this user']);
        }
        DB::beginTransaction();
        try{
            // $task = Task::where(['id' => $validated['task_id']])->first();
            $task->users()->detach($user);
            $task->refresh();
            // $task = Task::where(['id' => $task_id])->first();
            DB::commit();
            return $this->sendResponse($task, 'Deallocate Task Success');
        }catch(\Exception $e){
            DB::rollBack();
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

    public function delete(Request $request, int $task_id = -1){
        $user = Auth::guard('api')->user();
        $task = Project::where(['id' => $task_id])->first();
        $project = $task->projects;
        if(!isset($task)){
            return $this->sendError('Delete Task Fail', ['error' => 'Task is deleted or not exist']);
        }
        if(!$project->owners->contains($user) && !$project->editors->contains($user)){
            return $this->sendError('Delete Task Fail', ['error' => 'You have no permission']);
        }
        DB::beginTransaction();
        try{
            $task->delete();
            $task->save();
            DB::commit();
            return $this->sendResponse($task, 'Delete Task Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Fail', $e->getMessage());
        }
    }
}
