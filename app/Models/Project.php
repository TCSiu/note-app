<?php

namespace App\Models;

use App\Enum\ProjectPermissionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Task;
use App\Models\User;
use App\Traits\BaseDetail;
use App\Traits\DeleteRestore;
use App\Traits\ModelLog;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    // use HasFactory, Uuid, CreateUpdate;
    use HasFactory, BaseDetail, SoftDeletes, DeleteRestore;
    protected $tag_name = 'Project';
    protected $fillable = [
        'name',
        'description',
    ];

    public function tasks(){
        return $this->hasMany(Task::class, 'project_uuid', 'uuid');
    }

    public function users(){
        return $this->belongsToMany(User::class, 'users_projects', 'project_uuid', 'user_uuid', 'uuid', 'uuid');
    }
    public function owners(){
        return $this->belongsToMany(User::class, 'users_projects', 'project_uuid', 'user_uuid', 'uuid', 'uuid')->where(['permission' => ProjectPermissionEnum::OWNER]);
    }
    public function editors(){
        return $this->belongsToMany(User::class, 'users_projects', 'project_uuid', 'user_uuid', 'uuid', 'uuid')->where(['permission' => ProjectPermissionEnum::EDITOR]);
    }
    public function viewers(){
        return $this->belongsToMany(User::class, 'users_projects', 'project_uuid', 'user_uuid', 'uuid', 'uuid')->where(['permission' => ProjectPermissionEnum::VIEWER]);
    }

    // public function assign(User $user, int $permission_id){
    //     switch($permission_id){
    //         case(0):
    //             // $this->users()->attach($user->uuid, ['permission' => ProjectPermissionEnum::OWNER]);
    //             $this->users()->attach($user->uuid, ['project_uuid' => $this->uuid, 'permission' => ProjectPermissionEnum::OWNER]);
    //             break;
    //         case(1):
    //             // $this->users()->attach($user->uuid, ['permission' => ProjectPermissionEnum::EDITOR]);
    //             $this->users()->attach($user->uuid, ['project_uuid' => $this->uuid, 'permission' => ProjectPermissionEnum::EDITOR]);
    //             break;
    //         case(2):
    //             // $this->users()->attach($user->uuid, ['permission' => ProjectPermissionEnum::VIEWER]);
    //             $this->users()->attach($user->uuid, ['project_uuid' => $this->uuid, 'permission' => ProjectPermissionEnum::VIEWER]);
    //             break;
    //     }
    //     return $this;
    // }
}
