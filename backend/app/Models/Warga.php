<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warga extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'no_hp',
        'alamat',
        'rt_rw',
    ];

    // // RELASI
    // public function peminjamans()
    // {
    //     return $this->hasMany(Peminjaman::class);
    // }
}
