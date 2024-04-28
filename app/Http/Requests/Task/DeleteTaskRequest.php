<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\BaseRequest;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteTaskRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::guard('api')->user();
        $task = Project::where(['uuid' => $this->route('task_uuid')])->first();
        $project = $task->projects;
        if(!$project->owners->contains($user) && !$project->editors->contains($user)){
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
            //
        ];
    }

    public function handle() {
        $user = Auth::guard('api')->user();
        $task = Project::where(['uuid' => $this->route('task_uuid')])->first();
        $project = $task->projects;
        if(!isset($task)){
            return $this->sendError('Delete Task Fail', ['error' => 'Task is deleted or not exist']);
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
