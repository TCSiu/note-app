<?php

namespace App\Models;

use App\Traits\BaseDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLogs extends Model
{
    use HasFactory, BaseDetail;
    protected $table = 'system_logs';
    protected $fillable = [
        'system_logable_id',
        'system_logable_type',
        'user_id',
        'guard_name',
        'module_name',
        'action',
        'old_value',
        'new_value',
        'ip_address'
    ];

    // https://satyaprakash-nishad.medium.com/laravel-model-custom-logs-with-traits-89a246d8bf1c
}
