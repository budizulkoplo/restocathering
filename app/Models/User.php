<?php

namespace App\Models;
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'user';

    protected $fillable =
    [
        'username',
        'password',
        'name',
        'level',
        'foto'
    ];

    protected $hidden =
    [
        'password'
    ];
}
