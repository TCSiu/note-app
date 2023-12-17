<?php

namespace App\Models;

use App\Traits\BaseDetail;
use App\Traits\DeleteRestore;
use App\Traits\ModelLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    // use HasFactory, Uuid, CreateUpdate;
    use HasFactory, BaseDetail, SoftDeletes, DeleteRestore;

    protected $tag_name = 'Comment';
    protected $fillable = [
        // 'name',
        'description',
    ];

    public function tasks(){
        return $this->belongsTo(Task::class, 'task_uuid', 'uuid');
    }

    public function users(){
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
