<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\BaseRequest;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RestoreProjectRequest extends BaseRequest
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
        $project = Project::onlyTrashed()->where(['uuid' => $this->route('project_uuid')])->first();
        $user = Auth::guard('api')->user();
        if(!isset($project)){
            throw new HttpResponseException($this->notFound());
        }
        if(date('Y-m-d', strtotime('+1 month')) < date('Y-m-d')){
            throw new HttpResponseException($this->sendError('Restore Deleted Project Fail', ['error' => 'Project has been deleted after 1 month']));
        }
        if(!$project->owners->contains($user)){
            throw new HttpResponseException($this->sendError('Restore Deleted Project Fail', ['error' => 'You have no permission']));
        }
        DB::beginTransaction();
        try{
            $project->restore();
            $project->refresh();
            DB::commit();
            return $this->sendResponse($project, 'Restore Deleted Project Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Fail', $e->getMessage());
        }
    }
}
