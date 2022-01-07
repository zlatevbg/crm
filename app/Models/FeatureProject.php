<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeatureProject extends Model
{
    use SoftDeletes;

    protected $table =  'feature_project';

    protected $dates = [
        'deleted_at',
    ];
}
