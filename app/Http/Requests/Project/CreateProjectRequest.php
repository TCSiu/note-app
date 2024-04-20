<?php

namespace App\Http\Requests\Project;

use App\Enum\ProjectPermissionEnum;
use App\Http\Requests\BaseRequest;
use App\Models\Project;
use App\Models\Workflow;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateProjectRequest extends BaseRequest
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
            'name' => 'required|string',
            'description' => 'string|nullable',
            'workflow' => 'nullable|array',
            'workflow.*' => 'string',
        ];
    }

    public function handle() {
        $validated = $this->validated();
        $user = Auth::guard('api')->user();
        DB::beginTransaction();
        if(!isset($validated['workflow'])){
            $workflow = Workflow::find(1);
        } else {
            $workflow_list = [];
            $validated_workflow = json_encode(array_values($validated['workflow']));
            $workflow = Workflow::where(['workflow' => $validated_workflow])->first();
            if(!isset($workflow)){
                $workflow = Workflow::create(['workflow' => $validated_workflow]);
            }
            array_walk($validated['workflow'], function($value) use (&$workflow_list){
                $key = Str::uuid()->toString();
                $workflow_list[$key] = $value;
            });
            $validated['workflow'] = json_encode($workflow_list);
        }
        try{
            $project = Project::create($validated);
            $project->users()->attach($user->uuid, ['project_uuid' => $project->uuid, 'permission' => ProjectPermissionEnum::OWNER]);
            $workflow->projects()->save($project);
            DB::commit();
            return $this->sendResponse($project, 'Create Project Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Fail', $e->getMessage());
        }
    }
}
