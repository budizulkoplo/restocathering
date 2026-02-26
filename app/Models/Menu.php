<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    protected $table = 'menu';

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->with('children')
            ->orderBy('seq');
    }
}
