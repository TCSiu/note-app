<?php

namespace App\Commons;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Log;

class CommonFunction {

    public const MODEL_NAMESPACE = '\\App\\Models\\';
    public static function compareWorkflow(array $current_workflow, array $new_workflow) {
        if(array_diff($current_workflow, $new_workflow) || array_diff($new_workflow, $current_workflow) || array_diff_key($current_workflow, $new_workflow) || array_diff_key($new_workflow, $current_workflow)){
            return true;
        }
        return false;
    }

    public static function checkModel(string $model = "") {
        if (isset($model) && is_string($model)) {
            $model = trim($model);
            if (strlen($model) > 0) {
                $className = static::getModelClassName($model);
                if (class_exists($className)) {
                    return $className;
                }
            }
        }
        return false;
    }

    private static function getModelClassName(string $model = '')
    {
        return trim(static::MODEL_NAMESPACE).trim(str_replace(' ', '', static::getModelName($model)));
    }

    private static function getModelName(string $model = '')
    {
        return ucwords(trim($model));
    }
}