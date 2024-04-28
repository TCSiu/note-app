<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GetSuggestUserRequest extends BaseRequest
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
        if (!isset($project)) {
            return $this->sendError('Project not found', 'Project not found', 404);
        }
        $user = Auth::guard('api')->user();

        $current_users = $project->users;
        $user_list = [];

        $all_projects = $user->projects;
        $user_list = $all_projects->pluck('users')->flatten()->unique('uuid')->reject(function (User $value, $key) use ($user, $current_users) {
            return $current_users->contains($value) || $value->uuid == $user->uuid;
        })->values()->map(function (User $value, $key) {
            return $value->makeHidden(['pivot']);
        })->toArray();
        
        return $this->sendResponse($user_list, 'Get Suggested User List Success');
    }
}
