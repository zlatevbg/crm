<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadTag extends Model
{
    use SoftDeletes;

    protected $table =  'lead_tag';

    protected $dates = [
        'deleted_at',
    ];
}
