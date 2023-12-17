<?php

namespace App\Enum;

enum TaskStatusEnum:string{
    case WORKING = 'working';
    case COMMIT = 'commit';
    case UAT = 'uat';
    case DONE = 'done';
}