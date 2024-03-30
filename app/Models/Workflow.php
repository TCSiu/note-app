<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BaseDetail;
use App\Traits\DeleteRestore;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use HasFactory, BaseDetail, SoftDeletes, DeleteRestore;

    protected $tag_name = 'Workflow';

    protected $fillable = [
        'workflow',
    ];

    public function projects(){
        return $this->hasMany(Project::class, 'workflow_uuid', 'uuid');
    }
}
