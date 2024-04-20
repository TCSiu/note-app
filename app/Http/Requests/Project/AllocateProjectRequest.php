<?php

namespace App\Http\Requests\Project;

use App\Enum\ProjectPermissionEnum;
use App\Http\Requests\BaseRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class AllocateProjectRequest extends BaseRequest
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
            'user_uuid' => 'required_without:email|integer|exists:users,uuid',
            'email' => 'required_without:user_id|email|exists:users,email',
            'permission' => 'required|string',
        ];
    }

    public function handle() {
        $validated = $this->validated();

        $permission = ProjectPermissionEnum::tryFrom(strtolower($validated['permission']));
        if(!isset($permission)){
            throw new HttpResponseException($this->sendError('Assign Project Fail', ['error' => 'Unknown project permission'], 422));
        }

        $project = Project::where(['uuid' => $this->route('project_uuid')])->first();

        if(isset($validated['user_uuid'])){
            $user = User::where(['uuid' => $validated['user_uuid']])->first();
        } else if(isset($validated['email'])){
            $user = User::where(['email' => $validated['email']])->first();
        }

        if(!isset($project)){
            throw new HttpResponseException($this->notFound());
        }

        if($project->permission_check($permission, $user)){
            throw new HttpResponseException($this->sendError('Assign Project Fail', ['error' => 'Already assign to this user']));
        }
        
        DB::beginTransaction();
        try{
            $project->users()->detach($user);
            $project->users()->attach($user->uuid, ['project_uuid' => $project->uuid, 'permission' => $permission]);
            $project->refresh();
            DB::commit();
            return $this->sendResponse($project->users, 'Assign Project Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Create Task Fail', $e->getMessage());
        }
    }
}
