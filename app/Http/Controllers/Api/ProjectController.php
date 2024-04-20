<?php

namespace App\Http\Controllers\Api;

use App\Commons\CommonFunction;
use App\Enum\ProjectPermissionEnum;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Project\AllocateProjectRequest;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\DeallocateProjectRequest;
use App\Http\Requests\Project\DeleteProjectRequest;
use App\Http\Requests\Project\DeleteProjectWorkflowRequest;
use App\Http\Requests\Project\EditProjectRequest;
use App\Http\Requests\Project\GetProjectsRequest;
use App\Http\Requests\Project\GetSuggestUserRequest;
use App\Http\Requests\Project\GetTasksRequest;
use App\Http\Requests\Project\GetUserListRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\ViewProjectRequest;
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
    public function projects(GetProjectsRequest $request){
        return $request->handle();
    }
    public function assign(AllocateProjectRequest $request){
        return $request->handle();
    }

    public function deallocate(DeallocateProjectRequest $request){
        return $request->handle();
    }

    public function view(ViewProjectRequest $request){
        return $request->handle();
    }

    public function edit(EditProjectRequest $request){
        return $request->handle();
    }

    public function create(CreateProjectRequest $request){
        return $request->handle();
    }

    public function update(UpdateProjectRequest $request){
        return $request->handle();
    }

    public function delete(DeleteProjectRequest $request){
        return $request->handle();
    }

    public function restore(Request $request, int $project_id = -1){
        
    }

    public function tasks(GetTasksRequest $request){
        return $request->handle();
    }

    public function getSuggestUser(GetSuggestUserRequest $request){
        return $request->handle();
    }

    public function getUserList(GetUserListRequest $request){
        return $request->handle();
    }

    public function deleteWorkflow(DeleteProjectWorkflowRequest $request) {
        return $request->handle();
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
