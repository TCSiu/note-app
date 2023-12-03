<?php

namespace App\Models;

use App\Traits\CreateUpdate;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    // use HasFactory, Uuid, CreateUpdate;
    use HasFactory, Uuid;

    protected $fillable = [
        'name',
        'description',
    ];
}
