<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactTag extends Model
{
    use SoftDeletes;

    protected $table =  'contact_tag';

    protected $dates = [
        'deleted_at',
    ];
}
