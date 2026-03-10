<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
    protected $table = 'cuti';

    protected $fillable = [
        'tanggal',
        'pegawai_id',
        'pegawai_nama',
        'jabatan',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
