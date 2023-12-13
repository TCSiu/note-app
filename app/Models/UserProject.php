<?php

namespace App\Models;

use App\Enum\ProjectPermissionEnum;
use App\Traits\ModelLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProject extends Model
{
    use HasFactory, ModelLog;
    protected $tag_name = 'Users Projects';
    protected $table = 'users_projects';

    protected $casts = [
        'permission' => ProjectPermissionEnum::class,
    ];
}
