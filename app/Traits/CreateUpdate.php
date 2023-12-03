<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait CreateUpdate
{
    public static function bootCreatedUpdate(){
        parent::boot();
        self::creating(function ($model) {
            if (Schema::hasColumn($model, 'created_by')) {
                $model->created_by = Auth::id();
            }
            if (Schema::hasColumn('users', 'updated_by')) {
                $model->updated_by = Auth::id();
            }
        });
        self::updating(function ($model) {
            if (Schema::hasColumn($model, 'updated_by')) {
                $model->updated_by = Auth::id();
            }
        });
    }
}