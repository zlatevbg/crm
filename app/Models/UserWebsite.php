<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWebsite extends Model
{
    use SoftDeletes;

    protected $table =  'user_website';

    protected $dates = [
        'deleted_at',
    ];
}
