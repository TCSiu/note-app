<?php

namespace App\Http\Controllers\Api;

use App\Commons\CommonFunction;
use App\Enum\ProjectPermissionEnum;
use App\Http\Controllers\Api\BaseController;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProjectController extends BaseController
{
    public function index(Request $request){
        $user = Auth::guard('api')->user();
        $projects = $user->projects;
        return $this->sendResponse($projects, 'Get All the Projects');
    }
    public function assign(Request $request, int $project_id = -1){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required_without:email|integer|exists:users,id',
            'email' => 'required_without:user_id|email|exists:users,email',
            'permission' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to assign project', $validator->errors());
        }
        $validated = $validator->validated();

        $permission = ProjectPermissionEnum::tryFrom(strtolower($validated['permission']));
        if(!isset($permission)){
            return $this->sendError('Assign Project Fail', ['error' => 'Unknown project permission']);
        }

        $project = Project::where(['id' => $project_id])->first();

        if(isset($validated['user_id'])){
            $user = User::where(['id' => $validated['user_id']])->first();
        } else if(isset($validated['email'])){
            $user = User::where(['email' => $validated['email']])->first();
        }

        if(!isset($project)){
            return $this->notFound();
        }

        if($project->permission_check($permission, $user)){
            return $this->sendError('Assign Project Fail', ['error' => 'Already assign to this user']);
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

    public function deallocate(Request $request, int $project_id = -1){
        $validator = Validator::make($request->all(), [
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
            return $this->sendError('Deallocate Project Fail', ['error' => 'Can\'t deallocate owner']);
        }
        if(!$project->users->contains($user)){
            return $this->sendError('Deallocate Project Fail', ['error' => 'Already deallocate to this user']);
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
        if(!isset($project)){
            return $this->notFound();
        }
        if(!$project->users->contains($user)){
            return $this->sendError('View Project Fail', ['error' => 'User doesn\'t have permission to view this project']);
        }
        return $this->sendResponse($project, 'View Project Success');
    }

    public function edit(Request $request, $project_id = -1){
        $project = Project::where(['id' => $project_id])->first();
        $user = Auth::guard('api')->user();
        if(!isset($project)){
            return $this->notFound();
        }
        if(!$project->canEdit($user)){
            return $this->sendError('Edit Project Fail', ['error' => 'You have no permission to edit this project']);
        }
        $project = $project->makeHidden(['owners']);
        return $this->sendResponse($project, 'Edit Project Success');
    }

    public function create(Request $request){
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'description' => 'string|nullable',
            'workflow' => 'nullable|array',
            'workflow.*' => 'string',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to create project', $validator->errors());
        }
        $validated = $validator->validated();
        DB::beginTransaction();
        if(!isset($validated['workflow'])){
            $workflow = Workflow::find(1);
        } else {
            $workflow_list = [];
            $validated_workflow = json_encode(array_values($validated['workflow']));
            $workflow = Workflow::where(['workflow' => $validated_workflow])->first();
            if(!isset($workflow)){
                $workflow = Workflow::create(['workflow' => $validated_workflow]);
            }
            array_walk($validated['workflow'], function($value) use (&$workflow_list){
                $key = Str::uuid()->toString();
                $workflow_list[$key] = $value;
            });
            $validated['workflow'] = json_encode($workflow_list);
            
        }
        try{
            $project = Project::create($validated);
            $project->users()->attach($user->uuid, ['project_uuid' => $project->uuid, 'permission' => ProjectPermissionEnum::OWNER]);
            $workflow->projects()->save($project);
            DB::commit();
            return $this->sendResponse($project, 'Create Project Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Fail', $e->getMessage());
        }
    }

    public function update(Request $request, $project_id = -1){
        $user = Auth::guard('api')->user();
        $modified = $request->all();
        $modified['workflow'] = json_decode($modified['workflow'], true);
        $validator = Validator::make($modified, [
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'workflow' => 'nullable|array',
            'workflow.*' => 'string',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to update project', $validator->errors());
        }
        $validated = $validator->validated();
        $project = Project::where(['id' => $project_id])->with('workflowTemplate')->first();
        if(isset($project)){
            if(!($project->owners->contains($user) || $project->editors->contains($user))){
                return $this->sendError('Edit Project Fail', ['error' => 'You have no permission to edit this project']);
            }
        }else{
            return $this->notFound();
        }
        DB::beginTransaction();
        if(isset($validated['workflow'])){
            $current_workflow = json_decode($project->workflow, true);
            $workflow_change = CommonFunction::compareWorkflow($current_workflow, $validated['workflow']);
            $validated_workflow = json_encode(array_values($validated['workflow']));
            if($workflow_change){
                $workflow_list = [];
                $workflow_template = Workflow::where(['workflow' => $validated_workflow])->first();
                if(!isset($workflow_template)){
                    $workflow_template = Workflow::create(['workflow' => $validated_workflow]);
                }
                $validated['workflow_uuid'] = $workflow_template->uuid;
                array_walk($validated['workflow'], function($value, $key) use (&$workflow_list){
                    $key = Str::isUuid($key) ? $key : Str::uuid()->toString();
                    $workflow_list[$key] = $value;
                });
                $validated['workflow'] = $workflow_list;
            } else {
                $validated['workflow'] = json_encode($validated['workflow']);
            }
        }

        try{
            $project->update($validated);
            if(isset($workflow_change) && $workflow_change && isset($workflow_template)){
                $project->workflowTemplate()->associate($workflow_template)->save();
            }
            DB::commit();
            $project->refresh();
            $project = $project->makeHidden(['owners']);
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
            return $this->sendError('Delete Project Fail', ['error' => 'You have no permission']);
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
            return $this->sendError('Restore Deleted Project Fail', ['error' => 'Project has been deleted after 1 month']);
        }
        if(!$project->owners->contains($user)){
            return $this->sendError('Restore Deleted Project Fail', ['error' => 'You have no permission']);
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
        if(isset($project)){
            return $this->notFound();
        }
        $tasks = $project->tasks;
        $assign = $tasks->filter(function(Task $task, int $key) use($user){
            return $task->users->contains($user);
        });
        $unAssign = $tasks->filter(function(Task $task, int $key) use($user){
            return !$task->users->contains($user);
        });
        $data['assigned'] = $assign;
        $data['notAssign'] = $unAssign->values();
        $data['canEdit'] = $project->canEdit($user);
        return $this->sendResponse($data, 'Get Project Task Success');
    }

    public function getSuggestUser(Request $request, int $project_id){
        $project = Project::where(['id' => $project_id])->first();
        $user = Auth::guard('api')->user();

        $current_users = $project->users;
        $user_list = [];

        $all_projects = $user->projects;
        $user_list = $all_projects->pluck('users')->flatten()->unique('uuid')->reject(function (User $value, $key) use ($user, $current_users) {
            // return $value->uuid == $user->uuid;
            return $current_users->contains($value) || $value->uuid == $user->uuid;
        })->values()->map(function (User $value, $key) {
            return $value->makeHidden(['pivot']);
        })->toArray();
        
        return $this->sendResponse($user_list, 'Get Suggested User List Success');
    }

    public function getUserList(Request $request, int $project_id){
        $project = Project::where(['id' => $project_id])->first();
        return $this->sendRepsonse($project->users, 'Get Project User List Success');
    }

    public function deleteWorkflow(Request $request, int $project_id) {
        $project = Project::where(['id' => $project_id])->with('tasks')->first();
        $validator = Validator::make($request->all(), [
            'delete' => 'required|uuid',
            'move' => 'required|uuid',
        ]);
        $validated = $validator->validated();
        $workflow = json_decode($project->workflow, true);
        $workflow_keys = array_keys($workflow);
        if (!in_array($validated['delete'], $workflow_keys)) {
            return $this->sendError('Delete Project Workflow Fail', ['error' => 'Please select an existing project workflow to delete'], 400);    
        }
        if (!in_array($validated['move'], $workflow_keys)) {
            return $this->sendError('Create Task Fail', ['error' => 'Please select an existing project workflow to move'], 400);    
        }
        $workflow_name = $workflow[$validated['delete']];
        $tasks = Task::where(['workflow_uuid' => $validated['delete']])->get();
        DB::beginTransaction();
        try {
            foreach($tasks as $task) {
                $task['workflow_uuid'] = $validated['move'];
                $task->save();
            }
            DB::commit();
            $project->refresh();
            return $this->sendResponse($project, "Remove $workflow_name Success");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Assign Project Fail', $e->getMessage());
        }
    }

    public function assign_new(Request $request, int $project_id = -1){
        $validator = Validator::make($request->all(), [
            'email' => 'required|array',
            'email.*' => 'required|email|distinct|exists:users,email',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to assign project', $validator->errors());
        }
        $validated = $validator->validated();

        $error_messages = [];
        $project = Project::where(['id' => $project_id])->first();

        if(!isset($project)){
            return $this->notFound();
        }

        $permission = ProjectPermissionEnum::tryFrom('admin');

        DB::beginTransaction();
        try{
            $hasUpdate = false;
            foreach($validated['email'] as $email){
                $user = User::where(['email' => $email])->first();
                if(!$user){
                    $error_messages[] = "$email doesn't exists or already deleted";
                }else{
                    if($project->users->contains($user)){
                        $error_messages[] = "$email already in the assigned";
                    }else{
                        $hasUpdate = true;
                        $project->users()->attach($user->uuid, ['project_uuid' => $project->uuid, 'permission' => $permission]);
                    }
                }
            }
            $project->refresh();
            DB::commit();
            if(!$hasUpdate){
                if(empty($error_messages)){
                    return $this->sendResponse($project->users, 'No New Allocation');
                }else{
                    return $this->sendError('Assign Task Fail', $error_messages);
                }
            }
            $data['users'] = $project->users;
            $data['errors'] = $error_messages;
            return $this->sendResponse($data, empty($error_messages) ? 'Assign Project Success' : 'Assign Project Partially Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Assign Project Fail', $e->getMessage());
        }


        // if(!isset($project)){
        //     return $this->notFound();
        // }
        // if($project->users->contains($user)){
        //     return $this->sendError('Assign Project Fail', ['Already assign to this user']);
        // }
        // $permission = ProjectPermissionEnum::tryFrom($validated['permission']);
        // if(!isset($permission)){
        //     return $this->sendError('Assign Project Fail', 'Unknown project permission');
        // }
        // DB::beginTransaction();
        // try{
        //     // $project = Project::where(['id' => $validated['project_id']])->first();
        //     $project->users()->attach($user->uuid, ['project_uuid' => $project->uuid, 'permission' => $permission]);
        //     $project->refresh();
        //     DB::commit();
        //     // $project = $project->assign($user, $validated['permission']);
        //     // $project = Project::where(['id' => $validated['project_id']])->first();
        //     return $this->sendResponse($project->users, 'Assign Project Success');
        // }catch(\Exception $e){
        //     DB::rollBack();
        //     return $this->sendError('Create Task Fail', $e->getMessage());
        // }
    }
}
