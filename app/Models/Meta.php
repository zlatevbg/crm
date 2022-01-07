<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $table = 'meta';

    public $timestamps = false;

    protected $fillable = [
        'parent',
        'slug',
        'title',
        'description',
        'domain_id',
        'model',
    ];

    public $_parent;
}
