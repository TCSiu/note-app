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
    public function index(Request $request){
        $user = Auth::guard('api')->user();
        return $this->sendResponse($user->projects, 'Get All the Projects');
    }
    public function assign(Request $request, int $project_id = -1){
        $validator = Validator::make($request->all(), [
            // 'project_id' => 'required|integer|exists:projects,id',
            'user_id' => 'required|integer|exists:users,id',
            'permission' => 'required|string',
            // 'permission' => 'required|integer',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to assign project', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            // $project = Project::where(['id' => $validated['project_id']])->first();
            $project = Project::where(['id' => $project_id])->first();
            $user = User::where(['id' => $validated['user_id']])->first();
            if(!isset($project)){
                return $this->sendError('Assign Project Fail', ['Project is deleted or not exist']);
            }
            if($project->users->contains($user)){
                return $this->sendError('Assign Project Fail', ['Already assign to this user']);
            }
            $permission = ProjectPermissionEnum::tryFrom($validated['permission']);
            if(!isset($permission)){
                return $this->sendError('Assign Project Fail', 'Unknown project permission');
            }
            $project->users()->attach($user->uuid, ['project_uuid' => $project->uuid, 'permission' => $permission]);
            // $project = $project->assign($user, $validated['permission']);
            $project = Project::where(['id' => $validated['project_id']])->first();
            return $this->sendResponse($project->users, 'Assign Project Success');
        }catch(\Exception $e){
            return $this->sendError('Create Task Fail', $e->getMessage());
        }
    }

    public function deallocate(Request $request, int $project_id = -1){
        $validator = Validator::make($request->all(), [
            // 'project_id' => 'required|integer|exists:projects,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to deallocate project', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            // $project = Project::where(['id' => $validated['project_id']])->first();
            $project = Project::where(['id' => $project_id])->first();
            $user = User::where(['id' => $validated['user_id']])->first();
            if(!isset($project)){
                return $this->sendError('Deallocate Project Fail', ['Project is deleted or not exist']);
            }
            if($project->owners->contains($user)){
                return $this->sendError('Deallocate Project Fail', 'Can\'t deallocate owner');
            }
            if(!$project->users->contains($user)){
                return $this->sendError('Deallocate Project Fail', 'Already deallocate to this user');
            }
            $project->users()->detach($user);
            $project = Project::where(['id' => $validated['project_id']])->first();
            return $this->sendResponse($project->users, 'Deallocate Project Success');
        }catch(\Exception $e){
            return $this->sendError('Create Task Fail', $e->getMessage());
        }
    }

    public function view(Request $requset, $project_id = -1){
        $project = Project::where(['id' => $project_id])->first();
        $user = Auth::guard('api')->user();
        if(isset($project)){
            if(!$project->users->contains($user)){
                return $this->sendError('View Project Fail', ['User doesn\'t have permission to view this project']);
            }
            return $this->sendResponse($project, 'View Project Success');
        }
        return $this->sendError('View Project Fail', ['Project is deleted or not exist']);
    }

    public function edit(Request $request, $project_id = -1){
        $project = Project::where(['id' => $project_id])->first();
        $user = Auth::guard('api')->user();
        if(!isset($project)){
            return $this->sendError('Edit Project Fail', ['Project is deleted or not exist']);
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
            $project->assign($user, 0);
            return $this->sendResponse($project, 'Create Project Success');
        }catch(\Exception $e){
            return $this->sendError('Fail', $e->getMessage());
        }
    }

    public function update(Request $request, $project_id = -1){
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'name' => 'string|nullable',
            'description' => 'string|nullable',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to update project', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            $project = Project::where(['id' => $project_id])->first();
            if(isset($project)){
                if(!($project->owners->contains($user) || $project->editors->contains($user))){
                    return $this->sendError('Edit Project Fail', 'You have no permission to edit this project');
                }
            }else{
                return $this->sendError('Edit Project Fail', ['Project is deleted or not exist']);
            }
            $project = Project::where(['id' => $project_id])->update($validated);
            return $this->sendResponse($project, 'Update Project Success');
        }catch(\Exception $e){
            return $this->sendError('Fail', $e->getMessage());
        }
    }

    public function delete(Request $request, int $project_id = -1){
        $user = Auth::guard('api')->user();
        $project = Project::where(['id' => $project_id])->first();
        if(!isset($project)){
            return $this->sendError('Delete Project Fail', ['Project is deleted or not exist']);
        }
        if(!$project->users->contains($user)){
            return $this->sendError('Delete Project Fail', ['You have no permission']);
        }
        $project->delete();
        $project->save();
        return $this->sendResponse($project, 'Delete Project Success');
    }

    public function restore(Request $request, int $project_id = -1){

    }
}
