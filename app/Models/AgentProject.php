<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentProject extends Model
{
    use SoftDeletes;

    protected $table =  'agent_project';

    protected $dates = [
        'deleted_at',
    ];
}
