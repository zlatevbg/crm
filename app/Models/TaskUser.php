<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskUser extends Model
{
    use SoftDeletes;

    protected $table =  'task_user';

    protected $dates = [
        'deleted_at',
    ];
}
