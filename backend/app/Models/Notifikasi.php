<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasis';

    const TIPE_TERLAMBAT  = 'terlambat';
    const TIPE_STOK_HABIS = 'stok_habis';
    const TIPE_JATUH_TEMPO = 'jatuh_tempo'; // H-1 sebelum rencana kembali

    protected $fillable = [
        'peminjaman_id',
        'tipe',
        'pesan',
        'dibaca',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }

    // ── Scope ────────────────────────────────────────────────────────────

    public function scopeBelumDibaca($query)
    {
        return $query->where('dibaca', false);
    }
}
