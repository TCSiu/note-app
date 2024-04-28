<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\BaseRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class AssignTaskRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $validated = $this->validated();
        $task = Task::where(['uuid' => $this->route('task_uuid')])->first();
        $user = User::where(['uuid' => $validated['user_id']])->first();
        $project = $task->projects;
        if(!($project->owners->contains($user) || $project->editors->contains($user))){
            return false;
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_uuid' => 'required|integer|exists:users,uuid'
        ];
    }

    public function handle() {
        $validated = $this->validated();
        $task = Task::where(['uuid' => $this->route('task_uuid')])->first();
        $user = User::where(['uuid' => $validated['user_id']])->first();
        $project = $task->projects;
        if(!isset($project)){
            throw new HttpResponseException($this->sendError('Assign Task Fail', ['error' => 'Project doesn\'t exist']));
        }else{
            if(isset($project->deleted_at)){
                throw new HttpResponseException($this->sendError('Assign Task Fail', ['error' => 'Project is deleted']));
            }
        }
        if(!isset($task)){
            throw new HttpResponseException($this->sendError('Assign Task Fail', ['error' => 'Task is deleted or not exist']));
        }
        if(!isset($user)){
            throw new HttpResponseException($this->sendError('Assign Task Fail', ['error' => 'User is deleted or not exist']));
        }
        
        if($task->users->contains($user)){
            throw new HttpResponseException($this->sendError('Assign Task Fail', ['error' => 'Already assign to this user']));
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
}
