<?php

namespace App\Models;

use App\Enum\TaskStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Project;
use App\Traits\BaseDetail;
use App\Traits\CreateUpdate;
use App\Traits\Uuid;

class Task extends Model
{
    // use HasFactory, Uuid, CreateUpdate;
    use HasFactory, BaseDetail;

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
}
