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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    // use HasFactory, Uuid, CreateUpdate;
    use HasFactory, BaseDetail, SoftDeletes, DeleteRestore;
    protected $tag_name = 'Project';
    protected $fillable = [
        'name',
        'description',
        'workflow',
    ];

    protected $hidden = ['pivot'];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(){
        return $this->hasMany(Task::class, 'project_uuid', 'uuid')->with('creator')->with('attachments');
    }

    public function users(){
        return $this->belongsToMany(User::class, 'users_projects', 'project_uuid', 'user_uuid', 'uuid', 'uuid')->withPivot('permission');
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

    public function workflowTemplate(){
        return $this->belongsTo(Workflow::class, 'workflow_uuid', 'uuid');
    }

    public function attachments(): HasMany {
        return $this->hasMany(File::class, 'usage_uuid', 'uuid');
    }

    public function permission_check(ProjectPermissionEnum $permission, User $user){
        $permission_list = null;
        switch($permission){
            case ProjectPermissionEnum::OWNER:
                $permission_list = $this->owners;
                break;
            case ProjectPermissionEnum::EDITOR:
                $permission_list = $this->editors;
                break;
            case ProjectPermissionEnum::VIEWER:
                $permission_list = $this->viewers;
                break;
        }
        return $permission_list->contains($user);
    }

    public function canEdit(User | null $user){
        if($this->owners->contains($user) || $this->editors->contains($user)){
            return true;
        }
        return false;
    }
}
