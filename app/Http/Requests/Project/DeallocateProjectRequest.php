<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class DeallocateProjectRequest extends BaseRequest
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
            'user_id' => 'required|integer|exists:users,id',
        ];
    }

    public function handle() {
        $validated = $this->validated();
        $project = Project::where(['uuid' => $this->route('project_uuid')])->first();
        $user = User::where(['id' => $validated['user_id']])->first();
        if(!isset($project)){
            throw new HttpResponseException($this->notFound());
        }
        if($project->owners->contains($user)){
            throw new HttpResponseException($this->sendError('Deallocate Project Fail', ['error' => 'Can\'t deallocate owner']));
        }
        if(!$project->users->contains($user)){
            throw new HttpResponseException($this->sendError('Deallocate Project Fail', ['error' => 'Already deallocate to this user']));
        }
        DB::beginTransaction();
        try{
            $project->users()->detach($user);
            $project->refresh();
            DB::commit();
            return $this->sendResponse($project->users, 'Deallocate Project Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Create Task Fail', $e->getMessage());
        }
    }
}
