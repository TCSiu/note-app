<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\BaseRequest;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateTaskRequest extends BaseRequest
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
            'name' => 'required|string|max:255',
            'description' => 'required|string|nullable',
            'workflow_uuid' => 'required|uuid'
        ];
    }

    public function handle() {
        $user = Auth::guard('api')->user();
        $validated = $this->validated();
        $task = Task::where(['uuid' => $this->route('task_uuid')])->first();
        $project = $task->project;
        $workflow = json_decode($project->workflow, true);
        if(!isset($task)){
            throw new HttpResponseException($this->sendError('Update Task Fail', ['error' => 'Task is deleted or not exist']));
        }
        if (!in_array($validated['workflow_uuid'], $workflow)) {
            throw new HttpResponseException($this->sendError('Update Task Fail', ['error' => 'Please select an existing project workflow']));
        }
        DB::beginTransaction();
        try {
            $task->update($validated);
            DB::commit();
            return $this->sendResponse($task, 'Update Task Success');
        } catch(\Exception $e) {
            DB::rollBack();
            return $this->sendError('Update Task Fail', $e->getMessage());
        }
    }
}
