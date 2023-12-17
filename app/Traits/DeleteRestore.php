<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

trait DeleteRestore
{
    public static function bootDeleteRestore(){
        // parent::boot();        
        static::restoring(function (Model $model){
            if(Schema::hasColumn($model->getTable(), 'deleted_by')){
                $model->deleted_by = null;
            }
        });
        
        static::deleting(function (Model $model) {
            Log::info('deleting ' . $model);
            $model->timestamps = false;
            if(Schema::hasColumn($model->getTable(), 'deleted_by')){
                $model->deleted_by = Auth::id();
            }
        });
        
    }
}
