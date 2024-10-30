<?php

namespace App\Models;

use SMFramework\Auth\Authenticatable;

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
