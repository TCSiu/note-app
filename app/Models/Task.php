<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Project;
use App\Traits\CreateUpdate;
use App\Traits\Uuid;

class Task extends Model
{
    // use HasFactory, Uuid, CreateUpdate;
    use HasFactory, Uuid;

    protected $fillable = [
        'name',
        'description',
    ];

    public function projects(){
        return $this->belongsTo(Project::class, 'id', 'project_id');
    }
}
