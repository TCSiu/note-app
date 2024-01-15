<?php

namespace App\Http\Controllers\Api;

use App\Enum\ProjectPermissionEnum;
use App\Http\Controllers\Api\BaseController;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectController extends BaseController
{
    public function index(Request $request){
        $user = Auth::guard('api')->user();
        $projects = $user->projects;
        return $this->sendResponse($projects, 'Get All the Projects');
    }
    public function assign(Request $request, int $project_id = -1){
        $validator = Validator::make($request->all(), [
            // 'project_id' => 'required|integer|exists:projects,id',
            'user_id' => 'required_without:email|integer|exists:users,id',
            'email' => 'required_without:user_id|email|exists:users,email',
            'permission' => 'required|string',
            // 'permission' => 'required|integer',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to assign project', $validator->errors());
        }
        $validated = $validator->validated();
        $project = Project::where(['id' => $project_id])->first();
        if(isset($validated['user_id'])){
            $user = User::where(['id' => $validated['user_id']])->first();
        } else if(isset($validated['email'])){
            $user = User::where(['email' => $validated['email']])->first();
        }
        if(!isset($project)){
            return $this->notFound();
        }
        if($project->users->contains($user)){
            return $this->sendError('Assign Project Fail', ['Already assign to this user']);
        }
        $permission = ProjectPermissionEnum::tryFrom($validated['permission']);
        if(!isset($permission)){
            return $this->sendError('Assign Project Fail', 'Unknown project permission');
        }
        DB::beginTransaction();
        try{
            // $project = Project::where(['id' => $validated['project_id']])->first();
            $project->users()->attach($user->uuid, ['project_uuid' => $project->uuid, 'permission' => $permission]);
            $project->refresh();
            DB::commit();
            // $project = $project->assign($user, $validated['permission']);
            // $project = Project::where(['id' => $validated['project_id']])->first();
            return $this->sendResponse($project->users, 'Assign Project Success');
        }catch(\Exception $e){
            DB::rollBack();
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
        $project = Project::where(['id' => $project_id])->first();
        $user = User::where(['id' => $validated['user_id']])->first();
        if(!isset($project)){
            return $this->notFound();
        }
        if($project->owners->contains($user)){
            return $this->sendError('Deallocate Project Fail', 'Can\'t deallocate owner');
        }
        if(!$project->users->contains($user)){
            return $this->sendError('Deallocate Project Fail', 'Already deallocate to this user');
        }
        DB::beginTransaction();
        try{
            // $project = Project::where(['id' => $validated['project_id']])->first();
            $project->users()->detach($user);
            $project->refresh();
            DB::commit();
            // $project = Project::where(['id' => $validated['project_id']])->first();
            return $this->sendResponse($project->users, 'Deallocate Project Success');
        }catch(\Exception $e){
            DB::rollBack();
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
        return $this->notFound();
    }

    public function edit(Request $request, $project_id = -1){
        $project = Project::where(['id' => $project_id])->first();
        $user = Auth::guard('api')->user();
        if(!isset($project)){
            return $this->notFound();
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
        DB::beginTransaction();
        try{
            $project = Project::create($validated);
            $project->users()->attach($user->uuid, ['project_uuid' => $project->uuid, 'permission' => ProjectPermissionEnum::OWNER]);
            DB::commit();
            return $this->sendResponse($project, 'Create Project Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Fail', $e->getMessage());
        }
    }

    public function update(Request $request, $project_id = -1){
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to update project', $validator->errors());
        }
        $validated = $validator->validated();
        $project = Project::where(['id' => $project_id])->first();
        if(isset($project)){
            if(!($project->owners->contains($user) || $project->editors->contains($user))){
                return $this->sendError('Edit Project Fail', 'You have no permission to edit this project');
            }
        }else{
            return $this->notFound();
        }
        DB::beginTransaction();
        try{
            $project->update($validated);
            DB::commit();
            return $this->sendResponse($project, 'Update Project Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Fail', $e->getMessage());
        }
    }

    public function delete(Request $request, int $project_id = -1){
        $user = Auth::guard('api')->user();
        $project = Project::where(['id' => $project_id])->first();
        if(!isset($project)){
            return $this->notFound();
        }
        if(!$project->users->contains($user)){
            return $this->sendError('Delete Project Fail', ['You have no permission']);
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

    public function restore(Request $request, int $project_id = -1){
        $project = Project::onlyTrashed()->where(['id' => $project_id])->first();
        $user = Auth::guard('api')->user();
        if(!isset($project)){
            return $this->notFound();
        }
        if(date('Y-m-d', strtotime('+1 month')) < date('Y-m-d')){
            return $this->sendError('Restore Deleted Project Fail', ['Project has been deleted after 1 month']);
        }
        if(!$project->owners->contains($user)){
            return $this->sendError('Restore Deleted Project Fail', ['You have no permission']);
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

    public function tasks(Request $request, int $project_id = -1){
        $project = Project::where(['id'=> $project_id])->first();
        $user = Auth::guard('api')->user();
        $data = [];
        if(!isset($project)){
            return $this->notFound();
        }
        $tasks = $project->tasks;
        $assign = $tasks->filter(function(Task $task, int $key) use($user){
            return $task->users->contains($user);
        });
        $unAssign = $tasks->filter(function(Task $task, int $key) use($user){
            return !$task->users->contains($user);
        });
        $data['assign'] = $assign;
        $data['unAssign'] = $unAssign->values();
        $data['canEdit'] = $project->canEdit($user);
        return $this->sendResponse($data, 'Get Project Task Success');
    }
}
