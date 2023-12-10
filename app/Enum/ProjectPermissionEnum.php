<?php

namespace App\Enum;

enum ProjectPermissionEnum:string{
    case OWNER = 'owner';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';
}