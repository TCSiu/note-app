<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseRequest;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

class ViewProjectRequest extends BaseRequest
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
        $user = Auth::guard('api')->user();
        if(!isset($project)){
            throw new HttpResponseException($this->notFound());
        }
        if(!$project->users->contains($user)){
            throw new HttpResponseException($this->sendError('View Project Fail', ['error' => 'User doesn\'t have permission to view this project']));
        }
        $data = $project->toArray();
        $data['canEdit'] = $project->canEdit($user);
        return $this->sendResponse($data, 'View Project Success');
    }
}
