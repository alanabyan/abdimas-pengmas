<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfaqSubmission extends Model
{
    protected $fillable = [
        'nama',
        'telepon',
        'email',
        'jumlah',
        'jenis',
        'catatan',
        'status',
    ];

    protected $casts = [
        'jumlah' => 'integer',
    ];
}