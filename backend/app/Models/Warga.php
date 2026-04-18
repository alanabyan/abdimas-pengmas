<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warga extends Model
{
    use HasFactory;

    protected $table = 'wargas';

    protected $fillable = [
        'nama_warga',
        'no_hp',
        'alamat',
        'rt_rw',
    ];

    /** Semua riwayat peminjaman warga ini */
    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class, 'warga_id');
    }

    /** Peminjaman yang masih aktif */
    public function peminjamanAktif()
    {
        return $this->hasMany(Peminjaman::class, 'warga_id')
            ->whereIn('status', ['Menunggu', 'Aktif', 'Terlambat']);
    }

    // ── Scope ────────────────────────────────────────────────────────────

    public function scopeCari($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('nama_warga', 'like', "%{$keyword}%")
                ->orWhere('no_hp', 'like', "%{$keyword}%")
                ->orWhere('alamat', 'like', "%{$keyword}%")
                ->orWhere('rt_rw', 'like', "%{$keyword}%");
        });
    }
}
