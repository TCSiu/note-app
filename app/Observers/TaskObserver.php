<?php

namespace App\Observers;

use App\Commons\SystemLogFunction;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


class TaskObserver
{
    public function saved(Task $task){
        if($task->wasRecentlyCreated){
            if(isset($task->project_uuid)){
                SystemLogFunction::storeLog($task, Task::class, 'CREATED');
            }
        }else{
            if (!$task->getChanges()) {
                return;
            }
            if($task->wasChanged('deleted_at') ||$task->wasChanged('deleted_by')){
                return;
            }
            SystemLogFunction::storeLog($task, Task::class, 'UPDATED');
        }
    }

    public function deleted(Task $task){
        SystemLogFunction::storeLog($task, Task::class, 'DELETED');
    }

    public function restored(Task $task){
        SystemLogFunction::storeLog($task, Task::class, 'RESTORE');
    }
}
