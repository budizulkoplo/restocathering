<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    use SoftDeletes;

    protected $table = 'users';

    protected $guard_name = 'web';

    protected $fillable =
    [
        'username',
        'name',
        'email',
        'phone',
        'password',
        'level',
        'foto',
        'is_active',
        'last_login_at',
    ];

    protected $hidden =
    [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];
}
