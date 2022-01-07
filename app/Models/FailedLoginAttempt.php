<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\Domain;

class FailedLoginAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'ip',
        'domain',
        'type',
        'data',
    ];

    public static function record($event)
    {
        return static::create([
            'user_id' => $event->user ? $event->user->id : null,
            'ip' => request()->ip(),
            'domain' => Domain::domain(),
            'type' => 'failed-login',
            'data' => $event->credentials['email'] . ' | ' . $event->credentials['password'],
        ]);
    }
}
