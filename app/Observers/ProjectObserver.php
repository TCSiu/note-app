<?php

namespace App\Observers;

use App\Commons\SystemLogFunction;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

class ProjectObserver
{
    // public function saved(Project $project){
    //     Log::info($project);
    //     if($project->wasRecentlyCreated){
    //         SystemLogFunction::storeLog($project, Project::class, 'CREATED');
    //     }else{
    //         if (!$project->getChanges()) {
    //             return;
    //         }
    //         if($project->wasChanged('deleted_at') ||$project->wasChanged('deleted_by')){
    //             return;
    //         }
    //         SystemLogFunction::storeLog($project, Project::class, 'UPDATED');
    //     }
    // }

    public function created(Project $project){
        SystemLogFunction::storeLog($project, Project::class, 'CREATED');
    }

    public function updated(Project $project){
        if($project->wasChanged('deleted_at') ||$project->wasChanged('deleted_by')){
            return;
        }
        SystemLogFunction::storeLog($project, Project::class, 'UPDATED');
    }

    public function deleted(Project $project){
        SystemLogFunction::storeLog($project, Project::class, 'DELETED');
    }

    public function restored(Project $project){
        SystemLogFunction::storeLog($project, Project::class, 'RESTORE');
    }
}
