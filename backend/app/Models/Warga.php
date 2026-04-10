<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warga extends Model
{
    // Kasih tahu Laravel kalau Primary Key-nya bukan 'id' tapi 'id_warga'
    protected $primaryKey = 'id_warga';

    protected $fillable = [
        'nama_warga', 
        'no_hp', 
        'alamat', 
        'rt_rw'
    ];
}