<?php

namespace App\Observers;

use App\Commons\SystemLogFunction;
use App\Models\User;

class UserObserver
{
    public function saved(User $user){
        if($user->wasRecentlyCreated){
            SystemLogFunction::storeLog($user, User::class, 'CREATED');
        }else{
            if (!$user->getChanges()) {
                return;
            }
            if($user->wasChanged('deleted_at') ||$user->wasChanged('deleted_by')){
                return;
            }
            SystemLogFunction::storeLog($user, User::class, 'UPDATED');
        }
    }

    public function deleted(User $user){
        SystemLogFunction::storeLog($user, User::class, 'DELETED');
    }

    public function restored(User $user){
        SystemLogFunction::storeLog($user, User::class, 'RESTORE');
    }
}
