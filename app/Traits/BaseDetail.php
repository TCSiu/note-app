<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

trait BaseDetail
{
    public static function bootBaseDetail(){
        // parent::boot();
        self::creating(function (Model $model) {
            if (Schema::hasColumn($model->getTable(), 'uuid')) {
                $model->uuid = (string) Str::uuid();
            }
            if (Schema::hasColumn($model->getTable(), 'created_by')) {
                $model->created_by = Auth::id();
            }
            if (Schema::hasColumn($model->getTable(), 'updated_by')) {
                $model->updated_by = Auth::id();
            }
        });

        self::updating(function (Model $model) {
            if (Schema::hasColumn($model->getTable(), 'updated_by')) {
                $model->updated_by = Auth::id();
            }
        });        
    }
}
