<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QurbanSetting extends Model
{
    protected $table = 'qurban_settings';

    protected $fillable = [
        'periode',
        'harga_sapi',
        'kuota_total',
        'is_active',
    ];

    protected $casts = [
        'harga_sapi' => 'integer',
        'kuota_total' => 'integer',
        'is_active' => 'boolean',
    ];
}
