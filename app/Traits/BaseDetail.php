<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait BaseDetail
{
    public static function boot(){
        parent::boot();
        self::creating(function ($model) {
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
        self::updating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'updated_by')) {
                $model->updated_by = Auth::id();
            }
        });
        static::deleting(function ($model) {
			$model->timestamps = false;
			if(Schema::hasColumn($model->getTable(), 'is_deleted')){
				$model->is_deleted = 1;
			}
			$model->save();
        });
    }
}
