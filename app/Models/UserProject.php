<?php

namespace App\Models;

use App\Enum\ProjectPermissionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProject extends Model
{
    use HasFactory;
    protected $table = 'users_projects';

    protected $casts = [
        'permission' => ProjectPermissionEnum::class,
    ];
}
