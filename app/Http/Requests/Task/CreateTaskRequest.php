<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\BaseRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class CreateTaskRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'project_id' => 'required|integer|exists:projects,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|nullable',
            'workflow_uuid' => 'required|uuid',
        ];
    }

    public function handle() {
        $validated = $this->validated();
        $project = Project::where(['uuid' => $validated['project_id']])->first();
        $workflow = json_decode($project->workflow, true);
        if(!isset($project)){
            throw new HttpResponseException($this->sendError('Create Task Fail', ['error' => 'Please select an existing project'], 400));
        }
        if (!in_array($validated['workflow_uuid'], array_keys($workflow))) {
            throw new HttpResponseException($this->sendError('Create Task Fail', ['error' => 'Please select an existing project workflow'], 400));
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
}
