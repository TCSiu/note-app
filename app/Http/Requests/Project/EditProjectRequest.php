<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseRequest;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class EditProjectRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
        $project = Project::where(['uuid' => $this->route('project_uuid')])->first();
        $user = Auth::guard('api')->user();
        if(!isset($project)){
            throw new HttpResponseException($this->notFound());
        }
        if(!$project->canEdit($user)){
            throw new HttpResponseException($this->sendError('Edit Project Fail', ['error' => 'You have no permission to edit this project']));
        }
        $project = $project->makeHidden(['owners']);
        return $this->sendResponse($project, 'Edit Project Success');
    }
}
