<?php

namespace App\Models;

use App\Enum\TaskStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;
use App\Traits\BaseDetail;
use App\Traits\DeleteRestore;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    // use HasFactory, Uuid, CreateUpdate;
    use HasFactory, BaseDetail, SoftDeletes, DeleteRestore;
    protected $tag_name = 'Task';
    protected $fillable = [
        'name',
        'description',
        'workflow_uuid',
    ];

    protected $casts = [
        'status' => TaskStatusEnum::class,
    ];

    public function projects(){
        return $this->belongsTo(Project::class, 'project_uuid', 'uuid');
    }

    public function users(){
        return $this->belongsToMany(User::class, 'users_tasks', 'task_uuid', 'user_uuid', 'uuid', 'uuid');
    }

    public function comments(){
        return $this->hasMany(Comment::class, 'task_uuid', 'uuid');
    }

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
