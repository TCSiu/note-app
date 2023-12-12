<?php

namespace App\Models;

use App\Traits\BaseDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    // use HasFactory, Uuid, CreateUpdate;
    use HasFactory, BaseDetail;

    protected $fillable = [
        'name',
        'description',
    ];

    public function tasks(){
        return $this->belongsTo(Task::class, 'uuid', 'task_uuid');
    }
}
