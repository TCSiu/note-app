<?php

namespace App\Http\Controllers\Api;

use App\Enum\ProjectPermissionEnum;
use App\Http\Controllers\Api\BaseController;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends BaseController
{
    public function assignProject(Request $request){
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|integer|exists:projects,id',
            'user_id' => 'required|integer|exists:users,id',
            'permission' => 'required|integer',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to assign project', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            $project = Project::where(['id' => $validated['project_id'], 'is_deleted' => 0])->first();
            $user = User::where(['id' => $validated['user_id'], 'is_deleted' => 0, 'is_active' => 1])->first();
            if(!isset($project)){
                return $this->sendError('Assign Project Fail', ['Project is deleted']);
            }
            if($project->users->contains($user)){
                return $this->sendError('Assign Project Fail', ['Already assign to this user']);
            }
            $project = $project->assign($user, $validated['permission']);
            $project = Project::where(['id' => $validated['project_id'], 'is_deleted' => 0])->first();
            return $this->sendResponse($project->users, 'Assign Project Success');
        }catch(\Exception $e){
            return $this->sendError('Create Task Fail', $e->getMessage());
        }
    }

    public function deallocateProject(Request $request){
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|integer|exists:projects,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to deallocate project', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            $project = Project::where(['id' => $validated['project_id'], 'is_deleted' => 0])->first();
            $user = User::where(['id' => $validated['user_id'], 'is_deleted' => 0, 'is_active' => 1])->first();
            if(!isset($project)){
                return $this->sendError('Deallocate Project Fail', ['Project is deleted']);
            }
            if($project->owners->contains($user)){
                return $this->sendError('Deallocate Project Fail', 'Can\'t deallocate owner');
            }
            if(!$project->users->contains($user)){
                return $this->sendError('Deallocate Project Fail', 'Already deallocate to this user');
            }
            $project->users()->detach($validated['user_id']);
            $project = Project::where(['id' => $validated['project_id'], 'is_deleted' => 0])->first();
            return $this->sendResponse($project->users, 'Deallocate Project Success');
        }catch(\Exception $e){
            return $this->sendError('Create Task Fail', $e->getMessage());
        }
    }

    public function view(Request $requset, $project_id = -1){
        $project = Project::where(['id' => $project_id, 'is_deleted' => 0])->first();
        $user = Auth::guard('api')->user();
        if(isset($project)){
            if(!$project->users->contains($user)){
                return $this->sendError('View Project Fail', ['User doesn\'t have permission to view this project']);
            }
            return $this->sendResponse($project, 'View Project Success');
        }
        return $this->sendError('Create Task Fail', ['Project is deleted']);
    }

    public function edit(Request $request, $project_id = -1){
        $project = Project::where(['id' => $project_id, 'is_deleted' => 0])->first();
        $user = Auth::guard('api')->user();
        if(!isset($project)){
            return $this->sendError('Edit Project Fail', ['Project is deleted']);
        }
        if(!($project->owners->contains($user) || $project->editors->contains($user))){
            return $this->sendError('Edit Project Fail', ['You have no permission to edit this project']);
        }
        return $this->sendResponse($project, 'Edit Project Success');
    }

    public function create(Request $request){
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'description' => 'string|nullable',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to create project', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            $project = Project::create($validated);
            // dd($project->users);
            $project->assign($user, 0);
            return $this->sendResponse($project, 'Create Project Success');
        }catch(\Exception $e){
            return $this->sendError('Fail', $e->getMessage());
        }
    }

    public function store(Request $request, $project_id = -1){
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'description' => 'string|nullable',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to deallocate project', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            $project = Project::where(['id' => $project_id, 'is_deleted' => 0])->first();
            if(isset($project)){
                if(!($project->owners->contains($user) || $project->editors->contains($user))){
                    return $this->sendError('Edit Project Fail', 'You have no permission to edit this project');
                }
            }else{
                return $this->sendError('Edit Project Fail', ['Project is deleted']);
            }
            $project = Project::updateOrCreate(['id' => $project_id], $validated);
            return $this->sendResponse($project, 'Update Project Success');
        }catch(\Exception $e){
            return $this->sendError('Fail', $e->getMessage());
        }
    }

    public function delete(Request $request, int $project_id = -1){
        $user = Auth::guard('api')->user();
        $project = Project::where(['id' => $project_id, 'is_deleted' => 0])->first();
        if(!isset($project)){
            return $this->sendError('Delete Project Fail', ['Project is deleted']);
        }
        if(!$project->users->contains($user)){
            return $this->sendError('Delete Project Fail', ['You have no permission']);
        }
        $project->is_deleted = 1;
        $project->is_active = 0;
        $project->save();
        return $this->sendResponse($project, 'Delete Project Success');
    }

    // public function 
}
