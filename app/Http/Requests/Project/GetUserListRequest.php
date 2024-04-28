<?php

namespace App\Http\Requests\Project;

use App\Models\Project;
use App\Http\Requests\BaseRequest;

class GetUserListRequest extends BaseRequest
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
        $project = Project::where(['uuid' => $this->route('project_uuid')])->first();
        if (isset($project)) {
            return $this->sendResponse($project->users, 'Get Project User List Success');
        }
        return $this->sendError('Project Not Found', 'Project Not Found', 404);
    }
}
