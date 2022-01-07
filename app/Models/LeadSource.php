<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadSource extends Model
{
    use SoftDeletes;

    protected $table =  'lead_source';

    protected $dates = [
        'deleted_at',
    ];
}
