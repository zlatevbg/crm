<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvestorProject extends Model
{
    use SoftDeletes;

    protected $table =  'investor_project';

    protected $dates = [
        'deleted_at',
    ];
}
