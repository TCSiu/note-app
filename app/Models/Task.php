<?php

namespace App\Models;

use App\Enum\TaskStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Project;
use App\Traits\BaseDetail;
use App\Traits\CreateUpdate;
use App\Traits\DeleteRestore;
use App\Traits\ModelLog;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    // use HasFactory, Uuid, CreateUpdate;
    use HasFactory, BaseDetail, SoftDeletes, DeleteRestore;
    protected $tag_name = 'Task';
    protected $fillable = [
        'name',
        'description',
        'status',
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
}
