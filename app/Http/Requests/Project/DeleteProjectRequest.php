<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseRequest;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteProjectRequest extends BaseRequest
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
        $user = Auth::guard('api')->user();
        $project = Project::where(['uuid' => $this->route('project_uuid')])->first();
        if(!isset($project)){
            throw new HttpResponseException($this->notFound());
        }
        if(!$project->users->contains($user)){
            throw new HttpResponseException($this->sendError('Delete Project Fail', ['error' => 'You have no permission']));
        }
        DB::beginTransaction();
        try{
            $project->delete();
            $project->save();
            DB::commit();
            return $this->sendResponse($project, 'Delete Project Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Fail', $e->getMessage());
        }
    }
}
