<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class GetTasksRequest extends BaseRequest
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
            //
        ];
    }

    public function handle() {
        $project = Project::where(['uuid'=> $this->route('project_uuid')])->first();
        $user = Auth::guard('api')->user();
        $workflow_uuid = $this->workflow_uuid;
        $data = [];
        if(!isset($project)) {
            return $this->notFound();
        }
        $workflow = json_decode($project->workflow, true);
        if(isset($workflow_uuid) && !in_array($workflow_uuid, array_keys($workflow))) {
            return $this->sendError('Get Project Tasks Fail', ['error' => 'Task uuid doesn\'t exist in the project']);
        }
        $target_workflow = isset($workflow_uuid) ? $workflow_uuid : array_keys($workflow)[0];
        $tasks = $project->tasks;

        $data = $tasks->filter(function(Task $task) use ($target_workflow) {
            return $task->workflow_uuid == $target_workflow;
        })->toArray();
        return $this->sendResponse(array_values($data), 'Get Project Task Success');
    }
}
