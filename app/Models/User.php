<?php

namespace App\Models;

use LightWeight\Auth\Authenticatable;

class User extends Authenticatable
{
    protected array $hidden = ['password'];
    protected array $fillable = [
        'name',
        'lastname',
        'email',
        'password',
    ];
}
