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

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }

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

    public function canEdit($user){
        if($this->owners->contains($user) || $this->editors->contains($user)){
            return true;
        }
        return false;
    }
}
