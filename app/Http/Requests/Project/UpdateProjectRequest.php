<?php

namespace App\Http\Requests\Project;

use App\Commons\CommonFunction;
use App\Http\Requests\BaseRequest;
use App\Models\Project;
use App\Models\Workflow;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateProjectRequest extends BaseRequest
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
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'workflow' => 'nullable|array',
            'workflow.*' => 'string',
        ];
    }

    protected function prepareForValidation() {
        if (isset($this['workflow'])) {
            $this['workflow'] = json_decode($this['workflow'], true);
        }
    }

    public function handle() {
        $user = Auth::guard('api')->user();
        $validated = $this->validated();
        $project = Project::where(['uuid' => $this->route('project_uuid')])->with('workflowTemplate')->first();
        if(isset($project)){
            if(!($project->owners->contains($user) || $project->editors->contains($user))){
                throw new HttpResponseException($this->sendError('Edit Project Fail', ['error' => 'You have no permission to edit this project']));
            }
        }else{
            throw new HttpResponseException($this->notFound());
        }
        DB::beginTransaction();
        if(isset($validated['workflow'])){
            if (isset($project->workflow)) {
                $current_workflow = json_decode($project->workflow, true);
                $workflow_change = CommonFunction::compareWorkflow($current_workflow, $validated['workflow']);
            } else {
                $workflow_change = true;
            }
            if($workflow_change){
                $validated_workflow = json_encode(array_values($validated['workflow']));
                $workflow_list = [];
                $workflow_template = Workflow::where(['workflow' => $validated_workflow])->first();
                if(!isset($workflow_template)){
                    $workflow_template = Workflow::create(['workflow' => $validated_workflow]);
                }
                $validated['workflow_uuid'] = $workflow_template->uuid;
                array_walk($validated['workflow'], function($value, $key) use (&$workflow_list){
                    $key = Str::isUuid($key) ? $key : Str::uuid()->toString();
                    $workflow_list[$key] = $value;
                });
                $validated['workflow'] = $workflow_list;
            } else {
                $validated['workflow'] = json_encode($validated['workflow']);
            }
        }
        try{
            $project->update($validated);
            if(isset($workflow_change) && $workflow_change && isset($workflow_template)){
                $project->workflowTemplate()->associate($workflow_template)->save();
            }
            DB::commit();
            $project->refresh();
            $project = $project->makeHidden(['owners']);
            return $this->sendResponse($project, 'Update Project Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Fail', $e->getMessage());
        }
    }
}
