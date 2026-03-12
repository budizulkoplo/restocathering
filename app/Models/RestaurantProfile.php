<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantProfile extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'logo_path',
        'description',
    ];
}
