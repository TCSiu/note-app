<?php

namespace App\Commons;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Log;

class CommonFunction{
    public static function compareWorkflow(array $current_workflow, array $new_workflow){
        if(array_diff($current_workflow, $new_workflow) || array_diff($new_workflow, $current_workflow) || array_diff_key($current_workflow, $new_workflow) || array_diff_key($new_workflow, $current_workflow)){
            return true;
        }
        return false;
    }
}