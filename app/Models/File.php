<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BaseDetail;
use App\Traits\DeleteRestore;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, BaseDetail, SoftDeletes, DeleteRestore;

    protected $fillable = [
        'filename',
        'path',
        'size',
        'status',
        'usage',
        'usage_uuid',
    ];


}
