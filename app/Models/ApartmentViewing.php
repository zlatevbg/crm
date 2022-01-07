<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApartmentViewing extends Model
{
    use SoftDeletes;

    protected $table =  'apartment_viewing';

    protected $dates = [
        'deleted_at',
    ];
}
