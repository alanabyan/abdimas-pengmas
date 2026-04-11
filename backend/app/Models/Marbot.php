<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Marbot extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'marbots';

    protected $fillable = [
        'nama_marbot',
        'email',
        'password',
        'aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'aktif'             => 'boolean',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class, 'marbot_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
