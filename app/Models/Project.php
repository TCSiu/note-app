<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Task;
use App\Traits\CreateUpdate;
use App\Traits\Uuid;

class Project extends Model
{
    // use HasFactory, Uuid, CreateUpdate;
    use HasFactory, Uuid;

    protected $fillable = [
        'name',
        'description',
    ];

    public function tasks(){
        return $this->hasMany(Task::class, 'project_id', 'id');
    }
}
