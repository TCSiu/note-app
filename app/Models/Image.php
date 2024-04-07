<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BaseDetail;
use App\Traits\DeleteRestore;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use HasFactory, BaseDetail, SoftDeletes, DeleteRestore;

    protected $fillable = [
        'filename',
        'path',
        'size',
        'type',
        'status',
        'usage',
        'usage_uuid',
    ];

    // protected $hidden = [
    //     'path',
    // ];

    public function file_usage(): HasOne {
        return $this->hasOne($this->usage, 'uuid', 'usage_uuid');
    }
}
