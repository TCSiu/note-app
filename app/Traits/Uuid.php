<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait Uuid
{
    public static function boot(){
        parent::boot();
        self::creating(function ($model) {
            if (Schema::hasColumn($model, 'uuid')) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
