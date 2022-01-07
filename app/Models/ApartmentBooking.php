<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApartmentBooking extends Model
{
    use SoftDeletes;

    protected $table =  'apartment_booking';

    protected $dates = [
        'deleted_at',
    ];
}
