<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BaseDetail;
use App\Traits\DeleteRestore;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use HasFactory, BaseDetail, SoftDeletes, DeleteRestore;

    protected $fillable = [
        'imagePath',
    ];
}
