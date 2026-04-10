<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Marbot extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'marbots';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'aktif',
    ];

    protected $hidden = [
        'password',
    ];

    // RELASI
    // public function peminjamans()
    // {
    //     return $this->hasMany(Peminjaman::class);
    // }
}
