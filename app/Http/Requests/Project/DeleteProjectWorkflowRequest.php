<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class DeleteProjectWorkflowRequest extends BaseRequest
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
            'move' => 'required|uuid',
        ];
    }

    public function handle() {
        $project = Project::where(['uuid' => $this->route('project_uuid')])->with('tasks')->first();
        $validated = $this->validated();
        $target_workflow = $this->route('workflow_uuid');
        $workflows = json_decode($project->workflow, true);
        $workflow_keys = array_keys($workflows);
        if (!in_array($target_workflow, $workflow_keys)) {
            throw new HttpResponseException($this->sendError('Target Project Workflow Fail', ['error' => 'Please select an existing project workflow to delete'], 422));
        }
        if (!in_array($validated['move'], $workflow_keys)) {
            throw new HttpResponseException($this->sendError('Target Move Workflow Fail', ['error' => 'Please select an existing project workflow to move'], 422));
        }
        $workflow_name = $workflows[$target_workflow];
        unset($workflows[$validated['delete']]);
        $tasks = Task::where(['workflow_uuid' => $validated['delete']])->get();
        DB::beginTransaction();
        try {
            foreach($tasks as $task) {
                $task['workflow_uuid'] = $validated['move'];
                $task->save();
            }
            $project->workflow = $workflows;
            $project->save();
            DB::commit();
            $project->refresh();
            return $this->sendResponse($project, "Remove \"$workflow_name\" Success");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Assign Project Fail', $e->getMessage());
        }
    }
}
