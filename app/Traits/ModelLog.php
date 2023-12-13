<?php

namespace App\Traits;

use App\Models\SystemLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait ModelLog{
    public static function bootModelLog(){
        static::saved(function(Model $model){
            Log::info($model);
            if($model->wasRecentlyCreated){
                static::storeLog($model, static::class, 'CREATED');
            }else{
                if (!$model->getChanges()) {
                    return;
                }
                static::storeLog($model, static::class, 'UPDATED');
            }
            static::deleted(function (Model $model) {
                static::storeLog($model, static::class, 'DELETED');
            });
        });
    }

    public static function getTagName(Model $model){
        return !empty($model->tagName) ? $model->tagName : Str::title(Str::snake(class_basename($model), ''));
    }

    public static function activeUserId(){
        return Auth::guard(static::activeUserGuard())->id() ?? -1;
    }

    public static function activeUserGuard(){
        foreach(array_keys(config('auth.guards')) as $guard){
            if(auth()->guard($guard)->check()){
                return $guard;
            }
        }
        return null;
    }

    public static function storeLog($model, $model_path, $action){
        $oldValues = null;
        $newValues = null;
        if($action === 'CREATED'){
            $newValues = $model->getAttributes();
        }elseif($action === 'UPDATED'){
            $newValues = $model->getChanges();
        }
        if($action === 'DELETED'){
            $oldValues = $model->getOriginal();
        }
        $systemLog = new SystemLog();
        $systemLog->system_logable_id = $model->id;
        $systemLog->system_logable_type = $model_path;
        $systemLog->user_id = static::activeUserId();
        $systemLog->guard_name = static::activeUserGuard() ?? 'System';
        $systemLog->module_name = static::getTagName($model);
        $systemLog->action = $action;
        $systemLog->old_value = !empty($oldValues) ? json_encode($oldValues) : null;
        $systemLog->new_value = !empty($newValues) ? json_encode($newValues) : null;
        $systemLog->ip_address = request()->ip();
        $systemLog->save();
    }
}